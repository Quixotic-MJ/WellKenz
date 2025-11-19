@extends('Admin.layout.app')

@section('content')
<div class="space-y-6">

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
            <button onclick="document.getElementById('supplierModal').classList.remove('hidden')" 
                class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-plus mr-2"></i> Add New Supplier
            </button>
        </div>
    </div>

    {{-- 2. SEARCH & FILTERS --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search -->
        <div class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Search by Company, TIN, or Contact Person...">
        </div>
        
        <!-- Category Filter -->
        <div class="flex items-center gap-3 w-full md:w-auto">
             <select class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                <option value="">All Categories</option>
                <option value="raw">Raw Materials</option>
                <option value="packaging">Packaging</option>
                <option value="logistics">Logistics/Services</option>
            </select>
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
                    
                    {{-- Supplier 1 --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-700 font-bold border border-blue-200">
                                    GS
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">Golden Grain Supplies</div>
                                    <div class="text-xs text-gray-500 flex items-center mt-0.5">
                                        <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i> 
                                        123 Warehouse St., Cebu City
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mt-1">
                                        Raw Materials (Flour/Sugar)
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">Maria Santos</div>
                            <div class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-envelope mr-1"></i> sales@goldengrain.ph
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                <i class="fas fa-phone mr-1"></i> (032) 254-1234
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1">
                                <div class="flex items-center text-xs text-gray-600">
                                    <span class="w-16 font-semibold text-gray-500">TIN:</span> 
                                    <span class="font-mono">123-456-789-000</span>
                                </div>
                                <div class="flex items-center text-xs text-gray-600">
                                    <span class="w-16 font-semibold text-gray-500">Terms:</span> 
                                    <span class="bg-green-50 text-green-700 px-1.5 py-0.5 rounded border border-green-100 font-bold">Net 30 Days</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Active
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-blue-600 hover:text-blue-900 bg-blue-50 p-2 rounded hover:bg-blue-100 transition tooltip" title="Edit Details">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="text-gray-600 hover:text-gray-900 bg-gray-50 p-2 rounded hover:bg-gray-100 transition tooltip" title="View History">
                                <i class="fas fa-history"></i>
                            </button>
                        </td>
                    </tr>

                    {{-- Supplier 2 --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-10 w-10 bg-amber-100 rounded-lg flex items-center justify-center text-amber-700 font-bold border border-amber-200">
                                    PP
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">Prime Packaging Corp.</div>
                                    <div class="text-xs text-gray-500 flex items-center mt-0.5">
                                        <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i> 
                                        Mandaue Industrial Park
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mt-1">
                                        Boxes & Ribbons
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">John Doe</div>
                            <div class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-envelope mr-1"></i> j.doe@primepack.com
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                <i class="fas fa-phone mr-1"></i> 0917-123-4567
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1">
                                <div class="flex items-center text-xs text-gray-600">
                                    <span class="w-16 font-semibold text-gray-500">TIN:</span> 
                                    <span class="font-mono">987-654-321-000</span>
                                </div>
                                <div class="flex items-center text-xs text-gray-600">
                                    <span class="w-16 font-semibold text-gray-500">Terms:</span> 
                                    <span class="bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded border border-amber-100 font-bold">COD</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Active
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-blue-600 hover:text-blue-900 bg-blue-50 p-2 rounded hover:bg-blue-100 transition">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="text-gray-600 hover:text-gray-900 bg-gray-50 p-2 rounded hover:bg-gray-100 transition">
                                <i class="fas fa-history"></i>
                            </button>
                        </td>
                    </tr>

                    {{-- Supplier 3 (Inactive) --}}
                    <tr class="bg-gray-50 opacity-75">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-lg flex items-center justify-center text-gray-500 font-bold border border-gray-300">
                                    XX
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-500">Old Dairy Farm (Closed)</div>
                                    <div class="text-xs text-gray-400 flex items-center mt-0.5">
                                        <i class="fas fa-map-marker-alt mr-1 text-gray-300"></i> 
                                        Lahug, Cebu City
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500 mt-1">
                                        Dairy
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-500">N/A</div>
                            <div class="text-xs text-gray-400 mt-1">
                                <i class="fas fa-envelope mr-1"></i> contact@olddairy.com
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1">
                                <div class="flex items-center text-xs text-gray-500">
                                    <span class="w-16 font-semibold">TIN:</span> 
                                    <span class="font-mono">---</span>
                                </div>
                                <div class="flex items-center text-xs text-gray-500">
                                    <span class="w-16 font-semibold">Terms:</span> 
                                    <span>Unknown</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Inactive
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-gray-400 hover:text-gray-600 bg-white border border-gray-200 p-2 rounded">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <p class="text-sm text-gray-700">Showing <span class="font-medium">1</span> to <span class="font-medium">3</span> of <span class="font-medium">24</span> suppliers</p>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <button class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-chocolate text-white text-sm font-medium">1</button>
                    <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50">2</button>
                    <button class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </nav>
            </div>
        </div>
    </div>

</div>

<!-- ADD/EDIT SUPPLIER MODAL -->
<div id="supplierModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('supplierModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            
            <div class="bg-chocolate px-4 py-3 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">Add New Supplier</h3>
                <button onclick="document.getElementById('supplierModal').classList.add('hidden')" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="px-4 py-5 sm:p-6">
                <form>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        
                        <!-- Company Name -->
                        <div class="sm:col-span-4">
                            <label class="block text-sm font-medium text-gray-700">Company / Vendor Name <span class="text-red-500">*</span></label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-building text-gray-400"></i>
                                </div>
                                <input type="text" class="focus:ring-chocolate focus:border-chocolate block w-full pl-10 sm:text-sm border-gray-300 rounded-md" placeholder="e.g., Golden Grain Supplies">
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="blacklisted">Blacklisted</option>
                            </select>
                        </div>

                        <!-- TIN -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Tax ID No. (TIN)</label>
                            <input type="text" class="mt-1 focus:ring-chocolate focus:border-chocolate block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="000-000-000-000">
                        </div>

                        <!-- Payment Terms -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Payment Terms</label>
                            <select class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                <option>Cash on Delivery (COD)</option>
                                <option>Net 7 Days</option>
                                <option>Net 15 Days</option>
                                <option>Net 30 Days</option>
                                <option>Bank Transfer (Advance)</option>
                            </select>
                        </div>

                        <div class="sm:col-span-6 border-t border-gray-100 my-2"></div>

                        <!-- Contact Person -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Contact Person <span class="text-red-500">*</span></label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text" class="focus:ring-chocolate focus:border-chocolate block w-full pl-10 sm:text-sm border-gray-300 rounded-md" placeholder="Sales Representative">
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Phone / Mobile</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                                <input type="text" class="focus:ring-chocolate focus:border-chocolate block w-full pl-10 sm:text-sm border-gray-300 rounded-md" placeholder="(032) ...">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700">Email Address</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" class="focus:ring-chocolate focus:border-chocolate block w-full pl-10 sm:text-sm border-gray-300 rounded-md" placeholder="email@company.com">
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700">Full Address</label>
                            <div class="mt-1">
                                <textarea rows="3" class="shadow-sm focus:ring-chocolate focus:border-chocolate block w-full sm:text-sm border border-gray-300 rounded-md" placeholder="Unit No., Street, City, Province, Zip Code"></textarea>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Save Supplier
                </button>
                <button type="button" onclick="document.getElementById('supplierModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@endsection