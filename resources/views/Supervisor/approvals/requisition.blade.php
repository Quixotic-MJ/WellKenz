@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & SUMMARY --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Requisition Approvals</h1>
            <p class="text-sm text-gray-500 mt-1">Review stock requests from the production team.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold">
                5 Pending
            </div>
            <div class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">
                12 Approved Today
            </div>
        </div>
    </div>

    {{-- 2. FILTERS --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search -->
        <div class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Search by requester or item...">
        </div>

        <!-- Filters -->
        <div class="flex items-center gap-3 w-full md:w-auto">
            <select class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                <option value="pending" selected>Status: Pending</option>
                <option value="approved">Status: Approved</option>
                <option value="rejected">Status: Rejected</option>
                <option value="all">All History</option>
            </select>
            <input type="date" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
        </div>
    </div>

    {{-- 3. REQUISITIONS TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Details</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requester</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items Requested</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    
                    {{-- Row 1: Standard --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">#REQ-1024</div>
                            <div class="text-xs text-gray-500 mt-1"><i class="far fa-clock mr-1"></i> 2 hours ago</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold text-xs border border-gray-300">
                                    JD
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">Baker John Doe</div>
                                    <div class="text-xs text-gray-500">Wedding Cake Production</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 font-medium">White Sugar</div>
                            <div class="flex items-center gap-2 mt-1 text-xs">
                                <span class="text-gray-500">Requested: <span class="font-bold text-chocolate">50 kg</span></span>
                                <span class="text-gray-400">|</span>
                                <span class="text-gray-500">Stock: 150 kg</span>
                                <span class="text-green-600"><i class="fas fa-check-circle"></i></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs text-gray-500 italic bg-amber-50 border border-amber-100 p-2 rounded max-w-xs">
                                "Need this for the 3-tier wedding cake order #882 due tomorrow."
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                <button class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 border border-green-200 px-3 py-1 rounded transition" title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button onclick="openModifyModal('REQ-1024', 'White Sugar', 50, 'kg')" class="text-amber-600 hover:text-amber-900 bg-amber-50 hover:bg-amber-100 border border-amber-200 px-3 py-1 rounded transition" title="Modify">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 border border-red-200 px-3 py-1 rounded transition" title="Reject">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Row 2: High Volume Warning --}}
                    <tr class="hover:bg-red-50 transition-colors border-l-4 border-l-red-400">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">#REQ-1025</div>
                            <div class="text-xs text-gray-500 mt-1"><i class="far fa-clock mr-1"></i> 30 mins ago</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold text-xs border border-gray-300">
                                    MS
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">Maria (Pastry)</div>
                                    <div class="text-xs text-gray-500">Stock Replenishment</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 font-medium">Heavy Cream</div>
                            <div class="flex items-center gap-2 mt-1 text-xs">
                                <span class="text-gray-500">Requested: <span class="font-bold text-red-600">10 L</span></span>
                                <span class="text-gray-400">|</span>
                                <span class="text-gray-500">Stock: 12 L</span>
                                <span class="text-red-600 font-bold" title="High Request"><i class="fas fa-exclamation-triangle"></i></span>
                            </div>
                            <div class="text-[10px] text-red-500 font-medium mt-0.5">Consumes 83% of stock</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs text-gray-400 italic">- No notes -</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                <button class="text-gray-400 bg-gray-100 border border-gray-200 px-3 py-1 rounded cursor-not-allowed" disabled title="Stock too low for full approval">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button onclick="openModifyModal('REQ-1025', 'Heavy Cream', 10, 'L')" class="text-amber-600 hover:text-amber-900 bg-amber-50 hover:bg-amber-100 border border-amber-200 px-3 py-1 rounded transition" title="Modify">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 border border-red-200 px-3 py-1 rounded transition" title="Reject">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Row 3: Standard --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">#REQ-1026</div>
                            <div class="text-xs text-gray-500 mt-1"><i class="far fa-clock mr-1"></i> 5 hours ago</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold text-xs border border-gray-300">
                                    RJ
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">Rico (Bread)</div>
                                    <div class="text-xs text-gray-500">Daily Production</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 font-medium">Bread Flour</div>
                            <div class="flex items-center gap-2 mt-1 text-xs">
                                <span class="text-gray-500">Requested: <span class="font-bold text-chocolate">25 kg</span></span>
                                <span class="text-gray-400">|</span>
                                <span class="text-gray-500">Stock: 200 kg</span>
                                <span class="text-green-600"><i class="fas fa-check-circle"></i></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs text-gray-400 italic">- No notes -</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                <button class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 border border-green-200 px-3 py-1 rounded transition" title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button onclick="openModifyModal('REQ-1026', 'Bread Flour', 25, 'kg')" class="text-amber-600 hover:text-amber-900 bg-amber-50 hover:bg-amber-100 border border-amber-200 px-3 py-1 rounded transition" title="Modify">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 border border-red-200 px-3 py-1 rounded transition" title="Reject">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <p class="text-sm text-gray-700">Showing <span class="font-medium">1</span> to <span class="font-medium">3</span> of <span class="font-medium">5</span> pending requests</p>
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

<!-- MODIFY QUANTITY MODAL -->
<div id="modifyModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModifyModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-edit text-amber-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Modify Requisition</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="modifyModalText">
                                Adjusting quantity for <strong>White Sugar</strong> in request <strong>#REQ-1024</strong>.
                            </p>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase">Requested Qty</label>
                                    <div class="mt-1 text-lg font-medium text-gray-900 line-through text-red-400" id="originalQtyDisplay">50 kg</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-chocolate uppercase">Approved Qty</label>
                                    <div class="mt-1 flex rounded-md shadow-sm">
                                        <input type="number" id="newQtyInput" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-l-md border border-gray-300 focus:ring-chocolate focus:border-chocolate sm:text-sm font-bold text-chocolate" value="50">
                                        <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm" id="unitDisplay">kg</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Reason for Modification <span class="text-red-500">*</span></label>
                                <select class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                    <option>Insufficent Stock</option>
                                    <option>Rationing (High Demand)</option>
                                    <option>Policy Limit Exceeded</option>
                                    <option>Other</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Remarks</label>
                                <textarea rows="2" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="e.g. Reduced to conserve stock for weekend."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Confirm & Approve
                </button>
                <button type="button" onclick="closeModifyModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openModifyModal(reqId, itemName, originalQty, unit) {
        document.getElementById('modifyModalText').innerHTML = `Adjusting quantity for <strong>${itemName}</strong> in request <strong>#${reqId}</strong>.`;
        document.getElementById('originalQtyDisplay').innerText = `${originalQty} ${unit}`;
        document.getElementById('newQtyInput').value = originalQty;
        document.getElementById('unitDisplay').innerText = unit;
        
        document.getElementById('modifyModal').classList.remove('hidden');
    }

    function closeModifyModal() {
        document.getElementById('modifyModal').classList.add('hidden');
    }
</script>

@endsection