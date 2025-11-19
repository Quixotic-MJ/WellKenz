@extends('Admin.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Backup & Restore</h1>
            <p class="text-sm text-gray-500 mt-1">Manage database snapshots and system file archives to ensure data safety.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-cog mr-2"></i> Settings
            </button>
            <button class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-database mr-2"></i> Create Backup Now
            </button>
        </div>
    </div>

    {{-- 2. STATUS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Last Successful Backup -->
        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm flex items-center">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600 mr-4">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Last Successful Backup</p>
                <p class="text-lg font-bold text-gray-900">Oct 24, 03:00 AM</p>
                <p class="text-xs text-gray-500">Automated Daily Schedule</p>
            </div>
        </div>

        <!-- Total Storage -->
        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm flex items-center">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-4">
                <i class="fas fa-hdd text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Storage Used</p>
                <p class="text-lg font-bold text-gray-900">450 MB / 5 GB</p>
                <p class="text-xs text-gray-500">12 Active Backups</p>
            </div>
        </div>

        <!-- Next Scheduled -->
        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm flex items-center">
            <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 mr-4">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Next Scheduled Run</p>
                <p class="text-lg font-bold text-gray-900">Oct 25, 03:00 AM</p>
                <p class="text-xs text-gray-500">In approx 14 hours</p>
            </div>
        </div>

    </div>

    {{-- 3. BACKUP HISTORY TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900">Existing Backups</h3>
            <div class="flex items-center space-x-2">
                <span class="text-xs text-gray-500">Auto-retention: Keeps last 30 days</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    
                    {{-- Backup 1 (Latest) --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <i class="fas fa-file-archive text-gray-400 mr-3 text-lg"></i>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">backup-2023-10-24-0300.zip</div>
                                    <span class="text-xs text-green-600 flex items-center mt-0.5"><i class="fas fa-check mr-1"></i> Verified</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Oct 24, 2023 <span class="text-xs text-gray-400">(03:00 AM)</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            45.2 MB
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                Automated
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <button class="text-gray-600 hover:text-chocolate transition bg-white border border-gray-200 px-3 py-1.5 rounded shadow-sm text-xs font-bold">
                                <i class="fas fa-download mr-1"></i> Download
                            </button>
                            <button onclick="document.getElementById('restoreModal').classList.remove('hidden')" class="text-red-600 hover:text-red-900 transition bg-red-50 border border-red-100 px-3 py-1.5 rounded shadow-sm text-xs font-bold">
                                <i class="fas fa-undo mr-1"></i> Restore
                            </button>
                        </td>
                    </tr>

                    {{-- Backup 2 (Manual) --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <i class="fas fa-file-archive text-gray-400 mr-3 text-lg"></i>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">backup-2023-10-23-1545.zip</div>
                                    <span class="text-xs text-green-600 flex items-center mt-0.5"><i class="fas fa-check mr-1"></i> Verified</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Oct 23, 2023 <span class="text-xs text-gray-400">(03:45 PM)</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            44.8 MB
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                Manual
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <button class="text-gray-600 hover:text-chocolate transition bg-white border border-gray-200 px-3 py-1.5 rounded shadow-sm text-xs font-bold">
                                <i class="fas fa-download mr-1"></i> Download
                            </button>
                            <button onclick="document.getElementById('restoreModal').classList.remove('hidden')" class="text-red-600 hover:text-red-900 transition bg-red-50 border border-red-100 px-3 py-1.5 rounded shadow-sm text-xs font-bold">
                                <i class="fas fa-undo mr-1"></i> Restore
                            </button>
                        </td>
                    </tr>

                    {{-- Backup 3 (Auto) --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <i class="fas fa-file-archive text-gray-400 mr-3 text-lg"></i>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">backup-2023-10-23-0300.zip</div>
                                    <span class="text-xs text-green-600 flex items-center mt-0.5"><i class="fas fa-check mr-1"></i> Verified</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Oct 23, 2023 <span class="text-xs text-gray-400">(03:00 AM)</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            44.5 MB
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                Automated
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <button class="text-gray-600 hover:text-chocolate transition bg-white border border-gray-200 px-3 py-1.5 rounded shadow-sm text-xs font-bold">
                                <i class="fas fa-download mr-1"></i> Download
                            </button>
                            <button onclick="document.getElementById('restoreModal').classList.remove('hidden')" class="text-red-600 hover:text-red-900 transition bg-red-50 border border-red-100 px-3 py-1.5 rounded shadow-sm text-xs font-bold">
                                <i class="fas fa-undo mr-1"></i> Restore
                            </button>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
        <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 text-xs text-gray-500 flex justify-between items-center">
            <span>Showing recent 3 backups</span>
            <a href="#" class="text-chocolate hover:underline">View All History</a>
        </div>
    </div>

    {{-- 4. MANUAL RESTORE CARD --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Upload & Restore</h3>
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:bg-gray-50 transition cursor-pointer">
            <div class="mx-auto h-12 w-12 text-gray-400">
                <i class="fas fa-cloud-upload-alt text-3xl"></i>
            </div>
            <p class="mt-2 text-sm font-medium text-gray-900">Upload a backup file to restore</p>
            <p class="mt-1 text-xs text-gray-500">SQL or ZIP files only (Max 100MB)</p>
            <button class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none">
                Select File
            </button>
        </div>
    </div>

</div>

<!-- RESTORE CONFIRMATION MODAL -->
<div id="restoreModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('restoreModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Confirm System Restore
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to restore the database to this snapshot? 
                                <br><br>
                                <span class="font-bold text-red-600">Warning:</span> All current data created after <span class="font-bold">Oct 24, 03:00 AM</span> will be lost forever. The system will be in maintenance mode during this process.
                            </p>
                        </div>
                        <!-- Confirm Input -->
                        <div class="mt-4">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Type "RESTORE" to confirm:</label>
                            <input type="text" class="shadow-sm focus:ring-red-500 focus:border-red-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="">
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Yes, Restore Database
                </button>
                <button type="button" onclick="document.getElementById('restoreModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@endsection