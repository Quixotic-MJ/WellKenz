@extends('Admin.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-2">Supplier Management</h1>
            <p class="text-sm text-gray-500">Manage vendor profiles, contact details, and payment terms.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative group">
                <button onclick="toggleExportMenu()" class="inline-flex items-center justify-center px-5 py-2.5 bg-white border border-border-soft text-chocolate text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-caramel transition-all shadow-sm group">
                    <i class="fas fa-file-export mr-2 opacity-70 group-hover:opacity-100"></i> Export List
                    <i class="fas fa-chevron-down ml-2 text-xs opacity-70 group-hover:opacity-100"></i>
                </button>
                <div id="exportMenu" class="hidden absolute right-0 mt-2 w-48 bg-white border border-border-soft rounded-lg shadow-lg z-10">
                    <div class="py-2">
                        <button onclick="exportSuppliers('csv')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-cream-bg hover:text-chocolate transition-colors">
                            <i class="fas fa-file-csv mr-3 text-green-600"></i> Export as CSV
                        </button>
                        <button onclick="exportSuppliers('pdf')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-cream-bg hover:text-chocolate transition-colors">
                            <i class="fas fa-file-pdf mr-3 text-red-600"></i> Export as PDF
                        </button>
                    </div>
                </div>
            </div>
            <button onclick="openAddModal()" 
                class="inline-flex items-center justify-center px-5 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-plus mr-2"></i> Add New Supplier
            </button>
        </div>
    </div>

    {{-- 2. STATS OVERVIEW --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-caramel transition-colors">
            <div class="absolute right-0 top-0 h-full w-1 bg-chocolate"></div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Suppliers</p>
                <p class="font-display text-3xl font-bold text-chocolate mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-cream-bg flex items-center justify-center text-chocolate group-hover:scale-110 transition-transform">
                <i class="fas fa-building text-xl"></i>
            </div>
        </div>

        <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-green-200 transition-colors">
            <div class="absolute right-0 top-0 h-full w-1 bg-green-500"></div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Partners</p>
                <p class="font-display text-3xl font-bold text-green-600 mt-1">{{ $stats['active'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center text-green-600 group-hover:scale-110 transition-transform">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
        </div>

        <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-red-200 transition-colors">
            <div class="absolute right-0 top-0 h-full w-1 bg-red-500"></div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Inactive</p>
                <p class="font-display text-3xl font-bold text-red-600 mt-1">{{ $stats['inactive'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center text-red-600 group-hover:scale-110 transition-transform">
                <i class="fas fa-times-circle text-xl"></i>
            </div>
        </div>
    </div>

    {{-- 3. SEARCH & FILTERS --}}
    <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm">
        <form method="GET" action="{{ route('admin.suppliers.index') }}" class="flex flex-col md:flex-row items-center gap-4 w-full">
            <div class="relative w-full md:flex-1 group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" 
                    class="block w-full pl-11 pr-4 py-2.5 border border-gray-200 rounded-lg bg-cream-bg placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" 
                    placeholder="Search by Company, TIN, or Contact Person...">
            </div>
            
            <div class="w-full md:w-48 relative">
                <select name="status" onchange="this.form.submit()" 
                    class="block w-full py-2.5 px-3 border border-gray-200 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm appearance-none cursor-pointer">
                    <option value="all" {{ request('status') === 'all' || request('status') === '' ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <i class="fas fa-chevron-down text-xs"></i>
                </div>
            </div>
            
            <div class="flex gap-2 w-full md:w-auto">
                <button type="submit" class="px-5 py-2.5 bg-chocolate text-white rounded-lg hover:bg-chocolate-dark transition-all shadow-md font-medium text-sm flex-1 md:flex-none justify-center flex">
                    <i class="fas fa-search mr-2"></i> Search
                </button>
                
                @if(request('search') || (request('status') && request('status') !== 'all'))
                <a href="{{ route('admin.suppliers.index') }}" class="px-5 py-2.5 bg-white border border-border-soft text-gray-600 rounded-lg hover:bg-gray-50 transition-all font-medium text-sm flex-1 md:flex-none justify-center flex items-center">
                    <i class="fas fa-times mr-2"></i> Clear
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- 4. SUPPLIERS TABLE --}}
    <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-soft">
                <thead class="bg-cream-bg">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Company Details</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Primary Contact</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Business Info</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Status</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-border-soft">
                    @forelse($suppliers as $supplier)
                    <tr class="group hover:bg-cream-bg transition-colors duration-200 {{ !$supplier->is_active ? 'opacity-60 bg-gray-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-chocolate to-caramel rounded-lg flex items-center justify-center text-white font-bold text-sm shadow-sm ring-2 ring-white">
                                    {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-chocolate">{{ $supplier->name }}</div>
                                    <div class="text-xs text-gray-500 flex items-center mt-0.5">
                                        <i class="fas fa-map-marker-alt mr-1 text-caramel/60"></i> 
                                        {{ Str::limit($supplier->address ?? 'No address', 20) }}
                                        @if($supplier->city), {{ $supplier->city }}@endif
                                        @if($supplier->province), {{ $supplier->province }}@endif
                                        @if($supplier->postal_code) ({{ $supplier->postal_code }})@endif
                                    </div>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono bg-gray-100 text-gray-600 mt-1 border border-gray-200">
                                        {{ $supplier->supplier_code }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-700">{{ $supplier->contact_person ?? 'N/A' }}</div>
                            <div class="flex flex-col space-y-0.5 mt-1">
                                <div class="text-xs text-gray-500">
                                    <i class="fas fa-envelope mr-1.5 text-caramel/60"></i> {{ $supplier->email ?? '-' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    <i class="fas fa-phone mr-1.5 text-caramel/60"></i> {{ $supplier->phone ?? '-' }}
                                </div>
                                @if($supplier->mobile)
                                <div class="text-xs text-gray-500">
                                    <i class="fas fa-mobile-alt mr-1.5 text-caramel/60"></i> {{ $supplier->mobile }}
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1.5">
                                <div class="flex items-center text-xs text-gray-600">
                                    <span class="w-12 font-bold text-chocolate/70">TIN:</span> 
                                    <span class="font-mono bg-white px-1 rounded border border-border-soft">{{ $supplier->tax_id ?? '---' }}</span>
                                </div>
                                <div class="flex items-center text-xs text-gray-600">
                                    <span class="w-12 font-bold text-chocolate/70">Terms:</span> 
                                    @if($supplier->payment_terms)
                                        @if($supplier->payment_terms == 0)
                                            <span class="text-amber-700 font-bold">COD</span>
                                        @else
                                            <span class="text-green-700 font-bold">Net {{ $supplier->payment_terms }}</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                                @if($supplier->rating)
                                <div class="flex items-center text-xs mt-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $supplier->rating ? 'text-caramel' : 'text-gray-200' }} text-[10px]"></i>
                                    @endfor
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 inline-flex text-[10px] leading-5 font-bold uppercase tracking-wide rounded-full {{ $supplier->is_active ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                <button onclick="openEditModal({{ $supplier->id }})" class="text-chocolate hover:text-white hover:bg-chocolate p-2 rounded-lg transition-all tooltip" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="confirmToggleStatus({{ $supplier->id }}, '{{ $supplier->name }}', {{ $supplier->is_active ? 'true' : 'false' }})" class="text-amber-600 hover:text-white hover:bg-amber-600 p-2 rounded-lg transition-all tooltip" title="{{ $supplier->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas fa-{{ $supplier->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                                <button onclick="confirmDelete({{ $supplier->id }}, '{{ $supplier->name }}')" class="text-red-600 hover:text-white hover:bg-red-600 p-2 rounded-lg transition-all tooltip" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 border border-border-soft">
                                    <i class="fas fa-building text-chocolate/30 text-2xl"></i>
                                </div>
                                <h3 class="font-display text-lg font-bold text-chocolate">No suppliers found</h3>
                                <p class="text-sm text-gray-400 mt-1">Try adjusting your search or add a new supplier.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($suppliers->hasPages())
        <div class="bg-white px-6 py-4 border-t border-border-soft">
            {{ $suppliers->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

</div>

{{-- ===================== UI COMPONENTS ===================== --}}

<div id="supplierModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-border-soft">
            
            <div class="bg-chocolate px-6 py-4 flex justify-between items-center">
                <h3 class="font-display text-xl font-bold text-white" id="modal-title">Add New Supplier</h3>
                <button onclick="closeModal()" class="text-white/70 hover:text-white transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <div class="px-8 py-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <form id="supplierForm">
                    @csrf
                    <input type="hidden" id="supplierId" name="supplier_id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="space-y-4">
                            <h4 class="text-xs font-bold text-caramel uppercase tracking-widest border-b border-border-soft pb-2 mb-4">Company Profile</h4>
                            
                            <div>
                                <label class="block text-sm font-bold text-chocolate mb-1">Company Name <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-building text-gray-400"></i>
                                    </div>
                                    <input type="text" name="name" id="supplierName" required class="block w-full pl-10 border-gray-200 bg-cream-bg rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all" placeholder="e.g., Golden Grain Supplies">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-chocolate mb-1">Status</label>
                                    <select name="is_active" id="supplierStatus" class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-chocolate mb-1">Tax ID (TIN)</label>
                                    <input type="text" name="tax_id" id="supplierTaxId" class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all" placeholder="000-000-000">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-chocolate mb-1">Full Address</label>
                                <textarea rows="3" name="address" id="supplierAddress" class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all" placeholder="Street Address"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-chocolate mb-1">City</label>
                                    <input type="text" name="city" id="supplierCity" class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-chocolate mb-1">Province</label>
                                    <input type="text" name="province" id="supplierProvince" class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-chocolate mb-1">Postal Code</label>
                                <input type="text" name="postal_code" id="supplierPostalCode" class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all" placeholder="1234">
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="text-xs font-bold text-caramel uppercase tracking-widest border-b border-border-soft pb-2 mb-4">Contact & Terms</h4>

                            <div>
                                <label class="block text-sm font-bold text-chocolate mb-1">Contact Person</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <input type="text" name="contact_person" id="supplierContactPerson" class="block w-full pl-10 border-gray-200 bg-cream-bg rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all" placeholder="Sales Representative">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-chocolate mb-1">Phone</label>
                                    <input type="text" name="phone" id="supplierPhone" class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all" placeholder="(032) ...">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-chocolate mb-1">Mobile</label>
                                    <input type="text" name="mobile" id="supplierMobile" class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all" placeholder="+63 9XX ...">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-chocolate mb-1">Email</label>
                                <input type="email" name="email" id="supplierEmail" class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all" placeholder="@company.com">
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg border border-border-soft space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Payment Terms</label>
                                        <div class="relative">
                                            <input type="number" name="payment_terms" id="supplierPaymentTerms" min="0" class="block w-full border-gray-200 bg-white rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all" placeholder="Days">
                                            <span class="absolute right-3 top-2 text-xs text-gray-400">Days</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Credit Limit</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-xs text-gray-400">₱</span>
                                            <input type="number" name="credit_limit" id="supplierCreditLimit" min="0" step="0.01" class="block w-full pl-6 border-gray-200 bg-white rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Rating</label>
                                    <select name="rating" id="supplierRating" class="block w-full border-gray-200 bg-white rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all">
                                        <option value="">No Rating</option>
                                        <option value="5">⭐⭐⭐⭐⭐ (5 Stars)</option>
                                        <option value="4">⭐⭐⭐⭐ (4 Stars)</option>
                                        <option value="3">⭐⭐⭐ (3 Stars)</option>
                                        <option value="2">⭐⭐ (2 Stars)</option>
                                        <option value="1">⭐ (1 Star)</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-chocolate mb-1">Notes</label>
                                <textarea rows="2" name="notes" id="supplierNotes" class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all" placeholder="Internal notes..."></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-border-soft">
                <button type="button" id="saveBtn" onclick="saveSupplier()" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 bg-chocolate text-base font-bold text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    <i class="fas fa-save mr-2"></i> Save Supplier
                </button>
                <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-cream-bg hover:text-chocolate focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<div id="confirmationModal" class="hidden fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="closeConfirmation()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-border-soft">
            <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div id="confIconContainer" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i id="confIcon" class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-bold text-chocolate font-display" id="confTitle">Confirmation</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="confMessage">Are you sure you want to proceed?</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse border-t border-border-soft">
                <button type="button" id="confConfirmBtn" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 bg-red-600 text-base font-bold text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Confirm
                </button>
                <button type="button" onclick="closeConfirmation()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-cream-bg hover:text-chocolate focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="hidden fixed top-5 right-5 z-[70] max-w-sm w-full bg-white shadow-xl rounded-xl pointer-events-auto border border-border-soft overflow-hidden transform transition-all duration-300 ease-out translate-y-2 opacity-0">
    <div class="p-4 bg-cream-bg">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i id="toastIcon" class="fas fa-check-circle text-green-500 text-xl"></i>
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
                <p id="toastTitle" class="text-sm font-bold text-chocolate">Notification</p>
                <p id="toastMessage" class="mt-1 text-sm text-gray-500"></p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
                <button onclick="hideToast()" class="inline-flex text-gray-400 hover:text-chocolate focus:outline-none transition-colors">
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
       DEBUGGING HELPERS
       =========================== */
    
    function debugSupplierOperation(operation, data = null) {
        if (window.localStorage.getItem('supplier_debug') === 'true') {
            console.log(`[SUPPLIER DEBUG] ${operation}`, data || {});
        }
    }
    
    function toggleDebugMode() {
        const current = window.localStorage.getItem('supplier_debug') === 'true';
        window.localStorage.setItem('supplier_debug', (!current).toString());
        showToast(
            'Debug Mode', 
            `Debug logging ${!current ? 'enabled' : 'disabled'}`, 
            'info'
        );
    }
    
    // Add debug mode toggle to console for easy access
    window.toggleSupplierDebug = toggleDebugMode;
    debugSupplierOperation('Script loaded');

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
        toastIcon.className = 'fas text-xl';
        
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
            btn.className = "w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 bg-red-600 text-base font-bold text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all";
            iconContainer.className = "mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10";
            icon.className = "fas fa-trash text-red-600";
            btn.textContent = "Delete";
        } else {
            // Warning/Toggle - Use Caramel theme
            btn.className = "w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 bg-caramel text-base font-bold text-white hover:bg-chocolate focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all";
            iconContainer.className = "mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 sm:mx-0 sm:h-10 sm:w-10";
            icon.className = "fas fa-exclamation-triangle text-amber-600";
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
        
        fetch(`{{ route('admin.suppliers.edit', ':id') }}`.replace(':id', id))
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                console.log('Edit modal data received:', data); // Debug logging
                
                if (!data.success || !data.supplier) {
                    console.error('Invalid response format:', data);
                    
                    // Handle different error scenarios
                    if (data.message) {
                        throw new Error(data.message);
                    }
                    
                    throw new Error('Invalid response format from server');
                }
                
                const supplier = data.supplier;
                console.log('Supplier data:', supplier); // Debug logging
                
                // Populate all form fields with error handling
                const fields = {
                    'supplierId': supplier.id,
                    'supplierName': supplier.name,
                    'supplierStatus': supplier.is_active ? '1' : '0',
                    'supplierTaxId': supplier.tax_id,
                    'supplierPaymentTerms': supplier.payment_terms,
                    'supplierContactPerson': supplier.contact_person,
                    'supplierPhone': supplier.phone,
                    'supplierMobile': supplier.mobile,
                    'supplierEmail': supplier.email,
                    'supplierAddress': supplier.address,
                    'supplierCity': supplier.city,
                    'supplierProvince': supplier.province,
                    'supplierPostalCode': supplier.postal_code,
                    'supplierRating': supplier.rating,
                    'supplierCreditLimit': supplier.credit_limit,
                    'supplierNotes': supplier.notes
                };
                
                // Set values with error checking
                Object.keys(fields).forEach(fieldId => {
                    const element = document.getElementById(fieldId);
                    if (element) {
                        element.value = fields[fieldId] || '';
                        console.log(`Set ${fieldId} to:`, fields[fieldId]);
                    } else {
                        console.warn(`Element with ID '${fieldId}' not found`);
                    }
                });
                
                console.log('Form fields populated successfully'); // Debug logging
                document.getElementById('supplierModal').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error loading supplier data:', error); // Debug logging
                console.error('Error details:', {
                    message: error.message,
                    stack: error.stack,
                    response: error.response,
                    status: error.status
                });
                showToast('Error', 'Failed to load supplier data: ' + error.message, 'error');
                
                // Show more detailed error for debugging
                if (error.response) {
                    error.response.json().then(errorData => {
                        console.error('Server error response:', errorData);
                    }).catch(() => {
                        console.error('Could not parse error response');
                    });
                }
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
            ? `{{ route('admin.suppliers.update', ':id') }}`.replace(':id', editSupplierId)
            : '{{ route('admin.suppliers.store') }}';
        const method = isEditMode ? 'PUT' : 'POST';
        
        // Collect form data manually for better control
        const data = {
            name: document.getElementById('supplierName').value,
            is_active: document.getElementById('supplierStatus').value === '1',
            tax_id: document.getElementById('supplierTaxId').value,
            payment_terms: parseInt(document.getElementById('supplierPaymentTerms').value) || 0,
            contact_person: document.getElementById('supplierContactPerson').value,
            phone: document.getElementById('supplierPhone').value,
            mobile: document.getElementById('supplierMobile').value,
            email: document.getElementById('supplierEmail').value,
            address: document.getElementById('supplierAddress').value,
            city: document.getElementById('supplierCity').value,
            province: document.getElementById('supplierProvince').value,
            postal_code: document.getElementById('supplierPostalCode').value,
            rating: parseInt(document.getElementById('supplierRating').value) || null,
            credit_limit: parseFloat(document.getElementById('supplierCreditLimit').value) || 0,
            notes: document.getElementById('supplierNotes').value
        };
        
        debugSupplierOperation('Saving supplier', {
            url: url,
            method: method,
            data: data,
            isEditMode: isEditMode,
            editSupplierId: editSupplierId
        });
        
        console.log('Saving supplier data:', {
            url: url,
            method: method,
            data: data,
            isEditMode: isEditMode,
            editSupplierId: editSupplierId
        });

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            // Handle different response types
            if (!response.ok) {
                // Try to parse error response
                return response.json().catch(() => {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }).then(errorData => {
                    // Add HTTP status to error data for better handling
                    errorData.http_status = response.status;
                    throw errorData;
                });
            }
            
            return response.json();
        })
        .then(result => {
            console.log('Save supplier response:', result); // Debug logging
            
            if (result.success) {
                closeModal();
                showToast('Success', result.message, 'success');
                
                console.log('Supplier saved successfully, reloading page...');
                if (isEditMode && result.supplier) {
                    console.log('Updated supplier data:', result.supplier);
                    // For edit mode, reload the page to show updated data
                    setTimeout(() => window.location.reload(), 1000);
                } else if (!isEditMode && result.supplier) {
                    // For new suppliers, we need to reload to show them in the list
                    setTimeout(() => window.location.reload(), 1000);
                }
            } else {
                console.error('Save failed:', result);
                
                // Handle validation errors more gracefully
                let errorMessage = result.message || 'Error saving supplier';
                
                if (result.errors) {
                    // Format validation errors nicely
                    const errorList = Object.values(result.errors).flat().join(', ');
                    errorMessage = errorList || 'Validation failed';
                }
                
                showToast('Validation Error', errorMessage, 'error');
            }
        })
        .catch(error => {
            console.error('Save supplier error:', error); // Debug logging
            
            let errorMessage = 'An unexpected error occurred';
            let errorTitle = 'Error';
            
            if (error.http_status) {
                switch(error.http_status) {
                    case 422:
                        errorTitle = 'Validation Error';
                        errorMessage = error.message || 'Please check your input and try again.';
                        break;
                    case 419:
                        errorTitle = 'Session Error';
                        errorMessage = 'Your session has expired. Please refresh the page and try again.';
                        break;
                    case 403:
                        errorTitle = 'Access Denied';
                        errorMessage = 'You do not have permission to perform this action.';
                        break;
                    case 404:
                        errorTitle = 'Not Found';
                        errorMessage = 'The requested supplier was not found.';
                        break;
                    case 500:
                        errorTitle = 'Server Error';
                        errorMessage = 'A server error occurred. Please try again later.';
                        break;
                    default:
                        errorMessage = error.message || `HTTP ${error.http_status} error occurred`;
                }
            } else if (error.message) {
                errorMessage = error.message;
            }
            
            showToast(errorTitle, errorMessage, 'error');
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
        fetch(`{{ route('admin.suppliers.toggle-status', ':id') }}`.replace(':id', id), {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            console.log('Toggle status response:', result); // Debug logging
            
            if (result.success) {
                showToast('Updated', result.message, 'success');
                
                // Try to update UI, but reload as fallback if it fails
                try {
                    updateSupplierRow(id, result.is_active);
                    updateStats(result.is_active);
                } catch (error) {
                    console.error('UI update failed, reloading page:', error);
                    setTimeout(() => window.location.reload(), 1000);
                }
                
                // Verify the change persisted after 2 seconds
                setTimeout(() => {
                    console.log('Status after update:', result.is_active); // Debug logging
                    // If UI doesn't reflect the change, reload the page
                    const statusBadge = document.querySelector(`button[onclick*="confirmToggleStatus(${id},"]`);
                    if (statusBadge) {
                        const row = statusBadge.closest('tr');
                        const badge = row?.querySelector('td:nth-child(4) span');
                        const expectedText = result.is_active ? 'Active' : 'Inactive';
                        if (badge && badge.textContent !== expectedText) {
                            console.log('UI not updated correctly, reloading page...');
                            window.location.reload();
                        }
                    }
                }, 2000);
            } else {
                console.error('Toggle status failed:', result);
                showToast('Error', result.message || 'Error updating status', 'error');
                // Reload page as fallback to show correct data
                setTimeout(() => window.location.reload(), 2000);
            }
        })
        .catch(error => {
            console.error('Toggle status error:', error); // Debug logging
            showToast('Error', 'Failed to update status: ' + error.message, 'error');
            // Reload page as fallback
            setTimeout(() => window.location.reload(), 2000);
        });
    }

    function updateSupplierRow(supplierId, newStatus) {
        // Find the supplier row by looking for data attribute or unique identifier
        const rows = document.querySelectorAll('tbody tr');
        let targetRow = null;
        let targetButton = null;
        
        rows.forEach(row => {
            const buttons = row.querySelectorAll('button[onclick*="confirmToggleStatus"]');
            buttons.forEach(button => {
                const onclickAttr = button.getAttribute('onclick');
                if (onclickAttr && onclickAttr.includes(`confirmToggleStatus(${supplierId},`)) {
                    targetRow = row;
                    targetButton = button;
                }
            });
        });
        
        if (!targetRow || !targetButton) {
            console.error('Could not find supplier row with ID:', supplierId);
            // Reload the page as fallback
            setTimeout(() => window.location.reload(), 1000);
            return;
        }
        
        // Update status badge (4th column)
        const statusBadge = targetRow.querySelector('td:nth-child(4) span');
        if (statusBadge) {
            statusBadge.textContent = newStatus ? 'Active' : 'Inactive';
            statusBadge.className = newStatus 
                ? 'px-2.5 py-0.5 inline-flex text-[10px] leading-5 font-bold uppercase tracking-wide rounded-full bg-green-50 text-green-700 border border-green-200'
                : 'px-2.5 py-0.5 inline-flex text-[10px] leading-5 font-bold uppercase tracking-wide rounded-full bg-red-50 text-red-700 border border-red-200';
        }
        
        // Update toggle button icon and tooltip
        const icon = targetButton.querySelector('i');
        if (icon) {
            if (newStatus) {
                icon.className = 'fas fa-ban';
                targetButton.title = 'Deactivate';
            } else {
                icon.className = 'fas fa-check';
                targetButton.title = 'Activate';
            }
        }
        
        // Update row styling for inactive state
        if (newStatus) {
            targetRow.classList.remove('opacity-60', 'bg-gray-50');
        } else {
            targetRow.classList.add('opacity-60', 'bg-gray-50');
        }
        
        // Update the confirmToggleStatus onclick attribute with current supplier name
        const companyNameElement = targetRow.querySelector('td:nth-child(1) .text-sm.font-bold.text-chocolate');
        const companyName = companyNameElement ? companyNameElement.textContent : 'Unknown Supplier';
        const newOnclick = `confirmToggleStatus(${supplierId}, '${companyName.replace(/'/g, "\\'")}', ${newStatus})`;
        targetButton.setAttribute('onclick', newOnclick);
        
        console.log('Updated supplier row:', supplierId, 'New status:', newStatus);
    }

    function updateStats(newStatus) {
        // Update the stats counters with more robust selectors
        const statsCards = document.querySelectorAll('.grid .bg-white.border.border-border-soft.rounded-xl.p-6.shadow-sm');
        let activeStat = null;
        let inactiveStat = null;
        
        statsCards.forEach(card => {
            if (card.querySelector('.text-green-600')) {
                activeStat = card.querySelector('.font-display');
            } else if (card.querySelector('.text-red-600')) {
                inactiveStat = card.querySelector('.font-display');
            }
        });
        
        if (activeStat && inactiveStat) {
            let activeCount = parseInt(activeStat.textContent) || 0;
            let inactiveCount = parseInt(inactiveStat.textContent) || 0;
            
            if (newStatus) {
                // Supplier was activated
                activeCount++;
                inactiveCount = Math.max(0, inactiveCount - 1);
            } else {
                // Supplier was deactivated
                inactiveCount++;
                activeCount = Math.max(0, activeCount - 1);
            }
            
            activeStat.textContent = activeCount;
            inactiveStat.textContent = inactiveCount;
            
            console.log('Updated stats - Active:', activeCount, 'Inactive:', inactiveCount);
        } else {
            console.warn('Could not find stats elements, reloading page...');
            setTimeout(() => window.location.reload(), 1000);
        }
    }

    function confirmDelete(id, name) {
        openConfirmation(
            'Delete Supplier?',
            `Are you sure you want to delete "${name}"? This action cannot be undone.`,
            'danger',
            () => deleteSupplier(id)
        );
    }

    /* ===========================
       EXPORT FUNCTIONALITY
       =========================== */

    function toggleExportMenu() {
        const menu = document.getElementById('exportMenu');
        menu.classList.toggle('hidden');
    }

    // Close export menu when clicking outside
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('exportMenu');
        const button = event.target.closest('button');
        
        if (!button || !button.onclick?.toString().includes('toggleExportMenu')) {
            if (!menu.contains(event.target)) {
                menu.classList.add('hidden');
            }
        }
    });

    function exportSuppliers(format) {
        // Hide the menu
        document.getElementById('exportMenu').classList.add('hidden');
        
        // Show loading toast
        showToast('Generating Export', `Preparing ${format.toUpperCase()} file...`, 'info');
        
        // Get current search and filter parameters
        const params = new URLSearchParams(window.location.search);
        
        // Build the export URL
        const exportUrl = format === 'csv' 
            ? '{{ route("admin.suppliers.export.csv") }}'
            : '{{ route("admin.suppliers.export.pdf") }}';
        
        // Add current parameters to export URL
        const fullUrl = exportUrl + (params.toString() ? '?' + params.toString() : '');
        
        // Create a temporary form to trigger the download
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = fullUrl;
        form.style.display = 'none';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add any additional form fields if needed
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
        
        // Update toast message
        setTimeout(() => {
            showToast('Export Started', `${format.toUpperCase()} export is being generated...`, 'info');
        }, 1000);
    }

    function deleteSupplier(id) {
        fetch(`{{ route('admin.suppliers.destroy', ':id') }}`.replace(':id', id), {
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