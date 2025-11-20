@extends('Employee.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Request History</h1>
            <p class="text-sm text-gray-500 mt-1">Track the status of your ingredient requisitions.</p>
        </div>
        <a href="{{ route('employee.requisitions.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
            <i class="fas fa-plus mr-2"></i> New Request
        </a>
    </div>

    {{-- 2. FILTERS --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search -->
        <div class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Search by Item or ID...">
        </div>

        <!-- Filter -->
        <div class="flex items-center gap-3 w-full md:w-auto">
            <select class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                <option value="all">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>

    {{-- 3. HISTORY TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Submitted</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items Summary</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisor Note</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    
                    {{-- Row 1: Pending --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-bold text-gray-700">
                            #REQ-1026
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Today, 10:00 AM
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">Bread Flour</div>
                            <div class="text-xs text-gray-500">25 kg</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                <i class="fas fa-clock mr-1"></i> Pending
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-400 italic">
                            - No action yet -
                        </td>
                    </tr>

                    {{-- Row 2: Approved --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-bold text-gray-700">
                            #REQ-1024
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Yesterday, 2:30 PM
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">White Sugar, Eggs</div>
                            <div class="text-xs text-gray-500">2 Items</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Approved
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-green-600">
                            <span class="flex items-center justify-end"><i class="fas fa-box-open mr-1"></i> Ready for Pickup</span>
                        </td>
                    </tr>

                    {{-- Row 3: Rejected (With Reason) --}}
                    <tr class="bg-red-50/30 hover:bg-red-50 transition-colors border-l-4 border-red-300">
                        <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-bold text-gray-700">
                            #REQ-1020
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Oct 20, 2023
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">Heavy Cream</div>
                            <div class="text-xs text-gray-500">20 L (High Qty)</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i> Rejected
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <button onclick="showRejectionReason('Excessive quantity for daily production. Please request smaller batch.', 'Supervisor Mike')" class="text-red-600 hover:text-red-800 font-bold text-xs underline">
                                Why?
                            </button>
                        </td>
                    </tr>

                    {{-- Row 4: Completed --}}
                    <tr class="hover:bg-gray-50 transition-colors opacity-75">
                        <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-bold text-gray-700">
                            #REQ-1018
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Oct 18, 2023
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">Butter (Unsalted)</div>
                            <div class="text-xs text-gray-500">5 kg</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                Completed
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-400">
                            Received
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- REJECTION REASON MODAL -->
<div id="rejectionModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('rejectionModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-comment-alt text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Rejection Details</h3>
                        <div class="mt-4 bg-red-50 p-3 rounded border border-red-100">
                            <p class="text-sm text-gray-800 italic" id="reasonText">"..."</p>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 text-right">- <span id="rejectorName">Supervisor</span></p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="document.getElementById('rejectionModal').classList.add('hidden')" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function showRejectionReason(reason, user) {
        document.getElementById('reasonText').innerText = `"${reason}"`;
        document.getElementById('rejectorName').innerText = user;
        document.getElementById('rejectionModal').classList.remove('hidden');
    }
</script>

@endsection