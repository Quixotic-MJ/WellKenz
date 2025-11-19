@extends('Admin.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Unit Configurations</h1>
            <p class="text-sm text-gray-500 mt-1">Define the standard measurements (Base Units) and container types (Packaging Units) used across the system.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="document.getElementById('unitModal').classList.remove('hidden')" 
                class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-plus mr-2"></i> Add New Unit
            </button>
        </div>
    </div>

    {{-- 2. INFORMATION CARD --}}
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-500"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">System Logic</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>
                        <strong>Base Units (Weight/Volume/Count):</strong> Used for <em>Recipes</em> and <em>Inventory Counting</em> (e.g., Grams, Liters).
                        <br>
                        <strong>Packaging Units:</strong> Used for <em>Purchasing</em> and <em>Delivery</em> (e.g., Sacks, Boxes).
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- 3. LEFT COL: STANDARD BASE UNITS (Often Fixed) --}}
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <i class="fas fa-ruler-combined text-gray-400 mr-2"></i> Standard Base Units
            </h3>
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Abbr.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <!-- Kilogram -->
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">Kilogram</td>
                            <td class="px-4 py-3 text-sm text-gray-600"><span class="bg-gray-100 px-2 py-1 rounded text-xs font-mono">kg</span></td>
                            <td class="px-4 py-3 text-sm text-gray-500">Weight</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-400"><i class="fas fa-lock" title="System Default"></i></td>
                        </tr>
                        <!-- Gram -->
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">Gram</td>
                            <td class="px-4 py-3 text-sm text-gray-600"><span class="bg-gray-100 px-2 py-1 rounded text-xs font-mono">g</span></td>
                            <td class="px-4 py-3 text-sm text-gray-500">Weight</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-400"><i class="fas fa-lock" title="System Default"></i></td>
                        </tr>
                        <!-- Liter -->
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">Liter</td>
                            <td class="px-4 py-3 text-sm text-gray-600"><span class="bg-gray-100 px-2 py-1 rounded text-xs font-mono">L</span></td>
                            <td class="px-4 py-3 text-sm text-gray-500">Volume</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-400"><i class="fas fa-lock" title="System Default"></i></td>
                        </tr>
                        <!-- Milliliter -->
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">Milliliter</td>
                            <td class="px-4 py-3 text-sm text-gray-600"><span class="bg-gray-100 px-2 py-1 rounded text-xs font-mono">mL</span></td>
                            <td class="px-4 py-3 text-sm text-gray-500">Volume</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-400"><i class="fas fa-lock" title="System Default"></i></td>
                        </tr>
                        <!-- Piece -->
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">Piece</td>
                            <td class="px-4 py-3 text-sm text-gray-600"><span class="bg-gray-100 px-2 py-1 rounded text-xs font-mono">pc</span></td>
                            <td class="px-4 py-3 text-sm text-gray-500">Count</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-400"><i class="fas fa-lock" title="System Default"></i></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 4. RIGHT COL: PACKAGING UNITS (Customizable) --}}
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <i class="fas fa-box-open text-chocolate mr-2"></i> Packaging / Purchase Units
            </h3>
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <!-- Sack -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">Sack</div>
                                <div class="text-xs text-gray-500">Usually 25kg or 50kg</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">Container</td>
                            <td class="px-4 py-3 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-medium">
                                <button class="text-blue-600 hover:text-blue-900"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <!-- Tin -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">Tin / Can</div>
                                <div class="text-xs text-gray-500">Metal container (Oil/Ghee)</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">Container</td>
                            <td class="px-4 py-3 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-medium">
                                <button class="text-blue-600 hover:text-blue-900"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <!-- Box -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">Box</div>
                                <div class="text-xs text-gray-500">Carton packaging</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">Container</td>
                            <td class="px-4 py-3 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-medium">
                                <button class="text-blue-600 hover:text-blue-900"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <!-- Tray -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">Tray</div>
                                <div class="text-xs text-gray-500">Eggs (30pcs usually)</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">Container</td>
                            <td class="px-4 py-3 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Inactive</span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-medium">
                                <button class="text-blue-600 hover:text-blue-900"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<!-- CREATE UNIT MODAL -->
<div id="unitModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('unitModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full">
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Add Packaging Unit</h3>
                        <div class="mt-4 space-y-4">
                            
                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Unit Name</label>
                                <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="e.g. Bottle, Bundle, Pack">
                            </div>

                            <!-- Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Type</label>
                                <select class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                    <option value="container">Container / Packaging</option>
                                    <option value="weight" disabled>Weight (System Standard)</option>
                                    <option value="volume" disabled>Volume (System Standard)</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Base units are managed by system admin only.</p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Short Description</label>
                                <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Optional notes">
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Save Unit
                </button>
                <button type="button" onclick="document.getElementById('unitModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@endsection