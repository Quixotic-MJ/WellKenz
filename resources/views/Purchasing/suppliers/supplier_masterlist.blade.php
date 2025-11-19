@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Vendor Masterlist</h1>
            <p class="text-sm text-gray-500 mt-1">Manage supplier profiles, contact details, and payment terms.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-file-export mr-2"></i> Export List
            </button>
            <button onclick="document.getElementById('supplierModal').classList.remove('hidden')" 
                class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-plus mr-2"></i> Add New Vendor
            </button>
        </div>
    </div>

    {{-- 2. SEARCH & FILTERS --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Search Vendor, TIN, or Contact...">
        </div>
        
        <div class="flex items-center gap-3 w-full md:w-auto">
             <select class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                <option value="">All Categories</option>
                <option value="raw">Raw Materials</option>
                <option value="packaging">Packaging</option>
                <option value="logistics">Services</option>
            </select>
        </div>
    </div>

    {{-- 3. VENDOR TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor Profile</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Primary Contact</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terms & Tax</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    
                    {{-- Vendor 1 --}}
                    <tr class="hover:bg-gray-50 transition-colors group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-700 font-bold border border-blue-200">
                                    GS
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">Golden Grain Supplies</div>
                                    <div class="text-xs text-gray-500 flex items-center mt-0.5">
                                        <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i> Cebu City
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 mt-1">
                                        Flour & Sugar
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">Maria Santos</div>
                            <div class="text-xs text-gray-500 mt-1"><i class="fas fa-phone mr-1"></i> (032) 254-1234</div>
                            <div class="text-xs text-gray-500"><i class="fas fa-envelope mr-1"></i> sales@goldengrain.ph</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-xs text-gray-500 mb-1">TIN: <span class="font-mono text-gray-700">123-456-789</span></div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800 border border-green-200">
                                Net 30 Days
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex justify-center items-center space-x-1 text-amber-400 text-xs">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star text-gray-300"></i>
                            </div>
                            <span class="text-[10px] text-gray-400">Reliable</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-chocolate hover:text-chocolate-dark bg-white border border-gray-200 hover:bg-gray-50 p-2 rounded transition">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>

                    {{-- Vendor 2 --}}
                    <tr class="hover:bg-gray-50 transition-colors group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-10 w-10 bg-amber-100 rounded-lg flex items-center justify-center text-amber-700 font-bold border border-amber-200">
                                    PP
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">Prime Packaging Corp.</div>
                                    <div class="text-xs text-gray-500 flex items-center mt-0.5">
                                        <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i> Mandaue City
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 mt-1">
                                        Boxes & Ribbons
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">John Doe</div>
                            <div class="text-xs text-gray-500 mt-1"><i class="fas fa-phone mr-1"></i> 0917-123-4567</div>
                            <div class="text-xs text-gray-500"><i class="fas fa-envelope mr-1"></i> j.doe@primepack.com</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-xs text-gray-500 mb-1">TIN: <span class="font-mono text-gray-700">987-654-321</span></div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                COD
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex justify-center items-center space-x-1 text-amber-400 text-xs">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="text-[10px] text-gray-400">Excellent</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-chocolate hover:text-chocolate-dark bg-white border border-gray-200 hover:bg-gray-50 p-2 rounded transition">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ADD VENDOR MODAL -->
<div id="supplierModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('supplierModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            
            <div class="bg-chocolate px-4 py-3 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">Add New Vendor</h3>
                <button onclick="document.getElementById('supplierModal').classList.add('hidden')" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="px-4 py-5 sm:p-6">
                <form>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <!-- Company Name -->
                        <div class="sm:col-span-4">
                            <label class="block text-sm font-medium text-gray-700">Company Name <span class="text-red-500">*</span></label>
                            <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>

                        <!-- Status -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select class="mt-1 block w-full border-gray-300 bg-white rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <!-- TIN -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Tax ID (TIN)</label>
                            <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>

                        <!-- Terms -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Payment Terms</label>
                            <select class="mt-1 block w-full border-gray-300 bg-white rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                <option>Net 30 Days</option>
                                <option>Net 15 Days</option>
                                <option>COD</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Save Vendor
                </button>
                <button type="button" onclick="document.getElementById('supplierModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection