@extends('Admin.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">General Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Configure core company details, branding, and system notifications.</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Save Button (Global) -->
            <button type="button" class="inline-flex items-center justify-center px-6 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-save mr-2"></i> Save Changes
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- LEFT COLUMN: NAVIGATION / ANCHORS (Optional, but good for UX) --}}
        <div class="hidden lg:block lg:col-span-1 space-y-6">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                <nav class="space-y-1">
                    <a href="#company-profile" class="flex items-center px-3 py-2 text-sm font-medium text-chocolate bg-orange-50 rounded-md group">
                        <i class="fas fa-building w-6 text-center mr-2"></i> Company Profile
                    </a>
                    <a href="#notifications" class="flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-md group">
                        <i class="fas fa-bell w-6 text-center mr-2"></i> Notifications
                    </a>
                    <a href="#finance" class="flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-md group">
                        <i class="fas fa-coins w-6 text-center mr-2"></i> Finance & Tax
                    </a>
                    <a href="#system" class="flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-md group">
                        <i class="fas fa-server w-6 text-center mr-2"></i> System Maintenance
                    </a>
                </nav>
            </div>

            <!-- System Info Card -->
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                <h4 class="text-sm font-bold text-blue-800 mb-2">System Information</h4>
                <div class="text-xs text-blue-700 space-y-1">
                    <p class="flex justify-between"><span>Version:</span> <span class="font-mono">v2.4.0</span></p>
                    <p class="flex justify-between"><span>Last Backup:</span> <span>Oct 23, 03:00 AM</span></p>
                    <p class="flex justify-between"><span>Timezone:</span> <span>Asia/Manila</span></p>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: FORMS --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- SECTION 1: COMPANY PROFILE --}}
            <div id="company-profile" class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Company Profile</h3>
                    <p class="mt-1 text-sm text-gray-500">This information will appear on Purchase Orders and Reports.</p>
                </div>
                <div class="p-6 space-y-6">
                    
                    <!-- Logo Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Company Logo</label>
                        <div class="mt-2 flex items-center">
                            <span class="h-16 w-16 rounded-lg overflow-hidden bg-gray-100 border border-gray-300 flex items-center justify-center">
                                <!-- Placeholder for current logo -->
                                <i class="fas fa-birthday-cake text-3xl text-gray-400"></i>
                            </span>
                            <button type="button" class="ml-5 bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate">
                                Change
                            </button>
                            <button type="button" class="ml-2 bg-white py-2 px-3 border border-transparent rounded-md text-sm leading-4 font-medium text-red-600 hover:bg-red-50 focus:outline-none">
                                Remove
                            </button>
                        </div>
                    </div>

                    <!-- Company Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Company Name</label>
                            <input type="text" value="WellKenz Cakes & Pastries" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Tax ID / TIN</label>
                            <input type="text" value="123-456-789-000" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Official Address</label>
                            <textarea rows="2" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">123 Baker Street, Culinary District, Cebu City, Philippines</textarea>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Contact Email</label>
                            <input type="email" value="admin@wellkenz.com" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Contact Phone</label>
                            <input type="text" value="(032) 123-4567" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: NOTIFICATION PREFERENCES --}}
            <div id="notifications" class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Notification Preferences</h3>
                    <p class="mt-1 text-sm text-gray-500">Control when and how the system alerts administrators.</p>
                </div>
                <div class="p-6 space-y-6">
                    
                    <div class="flex items-start justify-between">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="notif_lowstock" type="checkbox" checked class="focus:ring-chocolate h-4 w-4 text-chocolate border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="notif_lowstock" class="font-medium text-gray-700">Low Stock Alerts</label>
                                <p class="text-gray-500">Send email when an item reaches its reorder level.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start justify-between">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="notif_req" type="checkbox" checked class="focus:ring-chocolate h-4 w-4 text-chocolate border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="notif_req" class="font-medium text-gray-700">New Requisition Requests</label>
                                <p class="text-gray-500">Notify when staff submits a new stock request.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start justify-between">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="notif_expiry" type="checkbox" checked class="focus:ring-chocolate h-4 w-4 text-chocolate border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="notif_expiry" class="font-medium text-gray-700">Near Expiry Warnings</label>
                                <p class="text-gray-500">Daily summary of items expiring within 7 days.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start justify-between">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="notif_system" type="checkbox" class="focus:ring-chocolate h-4 w-4 text-chocolate border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="notif_system" class="font-medium text-gray-700">System Security Alerts</label>
                                <p class="text-gray-500">Notify on failed logins or suspicious activity immediately.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- SECTION 3: FINANCE & SYSTEM (Optional context added) --}}
            <div id="finance" class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Finance & System Defaults</h3>
                    <p class="mt-1 text-sm text-gray-500">Global settings for pricing and maintenance.</p>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Currency Symbol</label>
                        <select class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                            <option>₱ (PHP)</option>
                            <option>$ (USD)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Default Tax Rate (%)</label>
                        <input type="number" value="12" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                    </div>

                    <div class="col-span-2 border-t border-gray-100 pt-4 mt-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">Maintenance Mode</h4>
                                <p class="text-sm text-gray-500">Prevent non-admin users from accessing the system.</p>
                            </div>
                            <button type="button" class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate bg-gray-200" role="switch" aria-checked="false">
                                <span aria-hidden="true" class="translate-x-0 pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection