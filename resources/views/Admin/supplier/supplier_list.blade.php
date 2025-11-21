@extends('Admin.layout.app')

@section('content')
<div class="space-y-6 relative">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Supplier Management</h1>
            <p class="text-sm text-gray-500 mt-1">Manage vendor profiles, contact details, and payment terms.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-file-export mr-2"></i> Export List
            </button>
            <button onclick="openAddModal()" 
                class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-plus mr-2"></i> Add New Supplier
            </button>
        </div>
    </div>

    {{-- 2. SEARCH & FILTERS --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.suppliers.index') }}" class="flex flex-col md:flex-row items-center gap-4 w-full">
            <!-- Search -->
            <div class="relative w-full md:w-96">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Search by Company, TIN, or Contact Person...">
            </div>
            
            <!-- Status Filter -->
            <div class="flex items-center gap-3 w-full md:w-auto">
                 <select name="status" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            
            <button type="submit" class="px-4 py-2 bg-chocolate text-white rounded-md hover:bg-chocolate-dark transition">
                <i class="fas fa-search mr-1"></i> Search
            </button>
            
            @if(request('search') || request('status'))
            <a href="{{ route('admin.suppliers.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                <i class="fas fa-times mr-1"></i> Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-building text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Suppliers</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Active</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-lg">
                    <i class="fas fa-times-circle text-red-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Inactive</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['inactive'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. SUPPLIERS TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company Details</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Primary Contact</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business Info</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    
                    @forelse($suppliers as $supplier)
                    <tr class="hover:bg-gray-50 transition-colors {{ !$supplier->is_active ? 'bg-gray-50 opacity-75' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-10 w-10 {{ $supplier->is_active ? 'bg-blue-100' : 'bg-gray-200' }} rounded-lg flex items-center justify-center {{ $supplier->is_active ? 'text-blue-700' : 'text-gray-500' }} font-bold border {{ $supplier->is_active ? 'border-blue-200' : 'border-gray-300' }}">
                                    {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold {{ $supplier->is_active ? 'text-gray-900' : 'text-gray-500' }}">{{ $supplier->name }}</div>
                                    <div class="text-xs {{ $supplier->is_active ? 'text-gray-500' : 'text-gray-400' }} flex items-center mt-0.5">
                                        <i class="fas fa-map-marker-alt mr-1 {{ $supplier->is_active ? 'text-gray-400' : 'text-gray-300' }}"></i> 
                                        {{ $supplier->address ?? 'No address' }}{{ $supplier->city ? ', ' . $supplier->city : '' }}
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 {{ $supplier->is_active ? 'text-gray-800' : 'text-gray-500' }} mt-1">
                                        {{ $supplier->supplier_code }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium {{ $supplier->is_active ? 'text-gray-900' : 'text-gray-500' }}">{{ $supplier->contact_person ?? 'N/A' }}</div>
                            <div class="text-xs {{ $supplier->is_active ? 'text-gray-500' : 'text-gray-400' }} mt-1">
                                <i class="fas fa-envelope mr-1"></i> {{ $supplier->email ?? 'No email' }}
                            </div>
                            <div class="text-xs {{ $supplier->is_active ? 'text-gray-500' : 'text-gray-400' }} mt-0.5">
                                <i class="fas fa-phone mr-1"></i> {{ $supplier->phone ?? $supplier->mobile ?? 'No phone' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1">
                                <div class="flex items-center text-xs {{ $supplier->is_active ? 'text-gray-600' : 'text-gray-500' }}">
                                    <span class="w-16 font-semibold {{ $supplier->is_active ? 'text-gray-500' : '' }}">TIN:</span> 
                                    <span class="font-mono">{{ $supplier->tax_id ?? '---' }}</span>
                                </div>
                                <div class="flex items-center text-xs {{ $supplier->is_active ? 'text-gray-600' : 'text-gray-500' }}">
                                    <span class="w-16 font-semibold {{ $supplier->is_active ? 'text-gray-500' : '' }}">Terms:</span> 
                                    @if($supplier->payment_terms)
                                        @if($supplier->payment_terms == 0)
                                            <span class="bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded border border-amber-100 font-bold">COD</span>
                                        @else
                                            <span class="bg-green-50 text-green-700 px-1.5 py-0.5 rounded border border-green-100 font-bold">Net {{ $supplier->payment_terms }} Days</span>
                                        @endif
                                    @else
                                        <span>Unknown</span>
                                    @endif
                                </div>
                                @if($supplier->rating)
                                <div class="flex items-center text-xs text-gray-600">
                                    <span class="w-16 font-semibold text-gray-500">Rating:</span>
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $supplier->rating ? 'text-yellow-400' : 'text-gray-300' }} text-xs"></i>
                                    @endfor
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($supplier->is_active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick="openEditModal({{ $supplier->id }})" class="text-blue-600 hover:text-blue-900 bg-blue-50 p-2 rounded hover:bg-blue-100 transition tooltip" title="Edit Details">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="confirmToggleStatus({{ $supplier->id }}, '{{ $supplier->name }}', {{ $supplier->is_active ? 'true' : 'false' }})" class="text-{{ $supplier->is_active ? 'amber' : 'green' }}-600 hover:text-{{ $supplier->is_active ? 'amber' : 'green' }}-900 bg-{{ $supplier->is_active ? 'amber' : 'green' }}-50 p-2 rounded hover:bg-{{ $supplier->is_active ? 'amber' : 'green' }}-100 transition tooltip" title="{{ $supplier->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="fas fa-{{ $supplier->is_active ? 'ban' : 'check' }}"></i>
                            </button>
                            <button onclick="confirmDelete({{ $supplier->id }}, '{{ $supplier->name }}')" class="text-red-600 hover:text-red-900 bg-red-50 p-2 rounded hover:bg-red-100 transition tooltip" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-building text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500 text-lg">No suppliers found</p>
                                <p class="text-gray-400 text-sm mt-1">Try adjusting your search or add a new supplier</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($suppliers->hasPages())
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium">{{ $suppliers->firstItem() }}</span> to <span class="font-medium">{{ $suppliers->lastItem() }}</span> of <span class="font-medium">{{ $suppliers->total() }}</span> suppliers
                </p>
                {{ $suppliers->links() }}
            </div>
        </div>
        @endif
    </div>

</div>

{{-- ===================== UI COMPONENTS ===================== --}}

<!-- ADD/EDIT SUPPLIER MODAL -->
<div id="supplierModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            
            <div class="bg-chocolate px-4 py-3 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">Add New Supplier</h3>
                <button onclick="closeModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="px-4 py-5 sm:p-6">
                <form id="supplierForm">
                    @csrf
                    <input type="hidden" id="supplierId" name="supplier_id">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        
                        <!-- Company Name -->
                        <div class="sm:col-span-4">
                            <label class="block text-sm font-medium text-gray-700">Company / Vendor Name <span class="text-red-500">*</span></label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-building text-gray-400"></i>
                                </div>
                                <input type="text" name="name" id="supplierName" required class="focus:ring-chocolate focus:border-chocolate block w-full pl-10 sm:text-sm border-gray-300 rounded-md" placeholder="e.g., Golden Grain Supplies">
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="is_active" id="supplierStatus" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <!-- TIN -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Tax ID No. (TIN)</label>
                            <input type="text" name="tax_id" id="supplierTaxId" class="mt-1 focus:ring-chocolate focus:border-chocolate block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="000-000-000-000">
                        </div>

                        <!-- Payment Terms -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Payment Terms (Days)</label>
                            <input type="number" name="payment_terms" id="supplierPaymentTerms" min="0" class="mt-1 focus:ring-chocolate focus:border-chocolate block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="30">
                        </div>

                        <div class="sm:col-span-6 border-t border-gray-100 my-2"></div>

                        <!-- Contact Person -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text" name="contact_person" id="supplierContactPerson" class="focus:ring-chocolate focus:border-chocolate block w-full pl-10 sm:text-sm border-gray-300 rounded-md" placeholder="Sales Representative">
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Phone / Mobile</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                                <input type="text" name="phone" id="supplierPhone" class="focus:ring-chocolate focus:border-chocolate block w-full pl-10 sm:text-sm border-gray-300 rounded-md" placeholder="(032) ...">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700">Email Address</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" name="email" id="supplierEmail" class="focus:ring-chocolate focus:border-chocolate block w-full pl-10 sm:text-sm border-gray-300 rounded-md" placeholder="email@company.com">
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700">Full Address</label>
                            <div class="mt-1">
                                <textarea rows="2" name="address" id="supplierAddress" class="shadow-sm focus:ring-chocolate focus:border-chocolate block w-full sm:text-sm border border-gray-300 rounded-md" placeholder="Street Address"></textarea>
                            </div>
                        </div>

                        <!-- City & Province -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">City</label>
                            <input type="text" name="city" id="supplierCity" class="mt-1 focus:ring-chocolate focus:border-chocolate block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="City">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Province</label>
                            <input type="text" name="province" id="supplierProvince" class="mt-1 focus:ring-chocolate focus:border-chocolate block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Province">
                        </div>

                        <!-- Rating -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Rating (1-5)</label>
                            <select name="rating" id="supplierRating" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                <option value="">No Rating</option>
                                <option value="1">1 Star</option>
                                <option value="2">2 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="5">5 Stars</option>
                            </select>
                        </div>

                        <!-- Credit Limit -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Credit Limit</label>
                            <input type="number" name="credit_limit" id="supplierCreditLimit" min="0" step="0.01" class="mt-1 focus:ring-chocolate focus:border-chocolate block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="0.00">
                        </div>

                        <!-- Notes -->
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea rows="2" name="notes" id="supplierNotes" class="mt-1 shadow-sm focus:ring-chocolate focus:border-chocolate block w-full sm:text-sm border border-gray-300 rounded-md" placeholder="Additional notes..."></textarea>
                        </div>

                    </div>
                </form>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="saveBtn" onclick="saveSupplier()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    <i class="fas fa-save mr-2"></i> Save Supplier
                </button>
                <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- GENERIC CONFIRMATION MODAL -->
<div id="confirmationModal" class="hidden fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeConfirmation()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div id="confIconContainer" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i id="confIcon" class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="confTitle">Confirmation</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="confMessage">Are you sure you want to proceed?</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="confConfirmBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Confirm
                </button>
                <button type="button" onclick="closeConfirmation()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- TOAST NOTIFICATION -->
<div id="toast" class="hidden fixed top-5 right-5 z-[70] max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden transform transition-all duration-300 ease-out translate-y-2 opacity-0">
    <div class="p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i id="toastIcon" class="fas fa-check-circle text-green-400"></i>
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
                <p id="toastTitle" class="text-sm font-medium text-gray-900">Notification</p>
                <p id="toastMessage" class="mt-1 text-sm text-gray-500"></p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
                <button onclick="hideToast()" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">Close</span>
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    let isEditMode = false;
    let editSupplierId = null;
    let confirmActionCallback = null;

    /* ===========================
       UI HELPERS (TOAST & MODALS)
       =========================== */

    function showToast(title, message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastTitle = document.getElementById('toastTitle');
        const toastMsg = document.getElementById('toastMessage');
        const toastIcon = document.getElementById('toastIcon');

        toastTitle.textContent = title;
        toastMsg.textContent = message || '';

        // Reset classes
        toastIcon.className = 'fas';
        
        if(type === 'success') {
            toastIcon.classList.add('fa-check-circle', 'text-green-500');
        } else if(type === 'error') {
            toastIcon.classList.add('fa-times-circle', 'text-red-500');
        } else {
            toastIcon.classList.add('fa-info-circle', 'text-blue-500');
        }

        // Show
        toast.classList.remove('hidden');
        void toast.offsetWidth; // trigger reflow
        toast.classList.remove('translate-y-2', 'opacity-0');
        
        // Auto hide
        setTimeout(() => {
            hideToast();
        }, 3000);
    }

    function hideToast() {
        const toast = document.getElementById('toast');
        toast.classList.add('translate-y-2', 'opacity-0');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 300);
    }

    function openConfirmation(title, message, type, callback) {
        const modal = document.getElementById('confirmationModal');
        const titleEl = document.getElementById('confTitle');
        const msgEl = document.getElementById('confMessage');
        const btn = document.getElementById('confConfirmBtn');
        const iconContainer = document.getElementById('confIconContainer');
        const icon = document.getElementById('confIcon');

        titleEl.textContent = title;
        msgEl.textContent = message;
        confirmActionCallback = callback;

        // Style based on type (danger vs warning)
        if (type === 'danger') {
            btn.className = "w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm";
            iconContainer.className = "mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10";
            icon.className = "fas fa-trash text-red-600";
            btn.textContent = "Delete";
        } else {
            // Warning/Toggle
            btn.className = "w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-500 text-base font-medium text-white hover:bg-yellow-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm";
            iconContainer.className = "mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10";
            icon.className = "fas fa-exclamation-triangle text-yellow-600";
            btn.textContent = "Confirm";
        }

        modal.classList.remove('hidden');
    }

    function closeConfirmation() {
        document.getElementById('confirmationModal').classList.add('hidden');
        confirmActionCallback = null;
    }

    // Bind Generic Confirm Button
    document.getElementById('confConfirmBtn').addEventListener('click', function() {
        if (confirmActionCallback) {
            confirmActionCallback();
        }
        closeConfirmation();
    });

    /* ===========================
       SUPPLIER LOGIC
       =========================== */

    function openAddModal() {
        isEditMode = false;
        editSupplierId = null;
        document.getElementById('modal-title').textContent = 'Add New Supplier';
        document.getElementById('supplierForm').reset();
        document.getElementById('supplierId').value = '';
        document.getElementById('saveBtn').innerHTML = '<i class="fas fa-save mr-2"></i> Save Supplier';
        document.getElementById('supplierModal').classList.remove('hidden');
    }

    function openEditModal(id) {
        isEditMode = true;
        editSupplierId = id;
        document.getElementById('modal-title').textContent = 'Edit Supplier';
        document.getElementById('saveBtn').innerHTML = '<i class="fas fa-save mr-2"></i> Update Supplier';
        
        // Show loading toast while fetching
        // showToast('Loading...', 'Fetching supplier details', 'info');
        
        fetch(`{{ url('admin/suppliers') }}/${id}/edit`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                document.getElementById('supplierId').value = data.id;
                document.getElementById('supplierName').value = data.name || '';
                document.getElementById('supplierStatus').value = data.is_active ? '1' : '0';
                document.getElementById('supplierTaxId').value = data.tax_id || '';
                document.getElementById('supplierPaymentTerms').value = data.payment_terms || '';
                document.getElementById('supplierContactPerson').value = data.contact_person || '';
                document.getElementById('supplierPhone').value = data.phone || '';
                document.getElementById('supplierEmail').value = data.email || '';
                document.getElementById('supplierAddress').value = data.address || '';
                document.getElementById('supplierCity').value = data.city || '';
                document.getElementById('supplierProvince').value = data.province || '';
                document.getElementById('supplierRating').value = data.rating || '';
                document.getElementById('supplierCreditLimit').value = data.credit_limit || '';
                document.getElementById('supplierNotes').value = data.notes || '';
                
                document.getElementById('supplierModal').classList.remove('hidden');
            })
            .catch(error => {
                showToast('Error', 'Failed to load supplier data', 'error');
                console.error(error);
            });
    }

    function closeModal() {
        document.getElementById('supplierModal').classList.add('hidden');
    }

    function saveSupplier() {
        const form = document.getElementById('supplierForm');
        if(!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const btn = document.getElementById('saveBtn');
        const originalText = btn.innerHTML;
        
        // Loading state
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';

        const formData = new FormData(form);
        const url = isEditMode 
            ? `{{ url('admin/suppliers') }}/${editSupplierId}` 
            : '{{ route('admin.suppliers.store') }}';
        const method = isEditMode ? 'PUT' : 'POST';
        
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });
        // Ensure boolean/integer conversion matches backend expectation
        data.is_active = data.is_active === '1';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                closeModal();
                showToast('Success', result.message, 'success');
                setTimeout(() => window.location.reload(), 700);
            } else {
                showToast('Error', result.message || 'Error saving supplier', 'error');
            }
        })
        .catch(error => {
            showToast('Error', 'An unexpected error occurred', 'error');
            console.error(error);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }

    function confirmToggleStatus(id, name, currentStatus) {
        const action = currentStatus ? 'deactivate' : 'activate';
        openConfirmation(
            'Update Status?',
            `Are you sure you want to ${action} ${name}?`,
            'warning',
            () => toggleStatus(id)
        );
    }

    function toggleStatus(id) {
        fetch(`{{ url('admin/suppliers') }}/${id}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showToast('Updated', result.message, 'success');
                setTimeout(() => window.location.reload(), 700);
            } else {
                showToast('Error', result.message || 'Error updating status', 'error');
            }
        })
        .catch(error => {
            showToast('Error', 'Failed to update status', 'error');
            console.error(error);
        });
    }

    function confirmDelete(id, name) {
        openConfirmation(
            'Delete Supplier?',
            `Are you sure you want to delete "${name}"? This action cannot be undone.`,
            'danger',
            () => deleteSupplier(id)
        );
    }

    function deleteSupplier(id) {
        fetch(`{{ url('admin/suppliers') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showToast('Deleted', result.message, 'success');
                setTimeout(() => window.location.reload(), 700);
            } else {
                showToast('Error', result.message || 'Error deleting supplier', 'error');
            }
        })
        .catch(error => {
            showToast('Error', 'Failed to delete supplier', 'error');
            console.error(error);
        });
    }
</script>

@endsection