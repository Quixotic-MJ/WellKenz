@extends('Employee.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
            <p class="text-sm text-gray-500 mt-1">Manage alerts, system messages, and workflow approvals.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="text-sm text-chocolate hover:text-chocolate-dark font-bold mr-4">
                Mark all as read
            </button>
            <div class="bg-white border border-gray-300 rounded-lg px-3 py-2 shadow-sm flex items-center">
                <i class="fas fa-filter text-gray-400 mr-2"></i>
                <select class="text-sm font-medium text-gray-700 focus:outline-none bg-transparent border-none p-0">
                    <option value="all">All Notifications</option>
                    <option value="unread">Unread Only</option>
                    <option value="high">High Priority</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
        </div>
    </div>

    {{-- 2. NOTIFICATION LIST --}}
    <div class="space-y-3">

        {{-- ITEM 1: URGENT (Inventory Alert) --}}
        <!-- DB Data: 
             type: 'stock_alert'
             priority: 'urgent'
             is_read: false
             action_url: '/inventory/stock/count'
        -->
        <div class="bg-white border-l-4 border-red-500 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow relative group">
            <div class="absolute top-3 right-3 flex items-center space-x-2">
                <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-1 rounded uppercase tracking-wider">Urgent</span>
                <span class="text-xs text-gray-400">2 mins ago</span>
                <button class="text-gray-300 hover:text-chocolate"><i class="fas fa-ellipsis-v"></i></button>
            </div>
            
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center text-red-600">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="ml-4 flex-1 pr-20">
                    <h3 class="text-sm font-bold text-gray-900">Critical Stock Level: White Sugar</h3>
                    <p class="text-sm text-gray-600 mt-1">Current stock (5kg) is below the critical safety threshold. Immediate restocking required for scheduled production.</p>
                    
                    <div class="mt-3 flex items-center space-x-4">
                        <!-- Action URL Button -->
                        <a href="#" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none">
                            View Inventory
                        </a>
                        <span class="text-xs text-gray-400 font-mono">ID: #NTF-8821</span>
                    </div>
                </div>
                <!-- Unread Indicator -->
                <div class="absolute bottom-4 right-4">
                    <div class="w-3 h-3 bg-red-500 rounded-full border-2 border-white shadow-sm" title="Unread"></div>
                </div>
            </div>
        </div>

        {{-- ITEM 2: HIGH (Approval Request) --}}
        <!-- DB Data: 
             type: 'approval_req'
             priority: 'high'
             is_read: false
             action_url: '/supervisor/approvals/requisitions'
        -->
        <div class="bg-white border-l-4 border-amber-500 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow relative group">
            <div class="absolute top-3 right-3 flex items-center space-x-2">
                <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded uppercase tracking-wider">High</span>
                <span class="text-xs text-gray-400">1 hour ago</span>
                <button class="text-gray-300 hover:text-chocolate"><i class="fas fa-ellipsis-v"></i></button>
            </div>
            
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center text-amber-600">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                </div>
                <div class="ml-4 flex-1 pr-20">
                    <h3 class="text-sm font-bold text-gray-900">New Requisition: #REQ-1025</h3>
                    <p class="text-sm text-gray-600 mt-1">Baker John has requested <span class="font-medium text-gray-800">50kg Bread Flour</span>. This exceeds the daily average limit.</p>
                    
                    <div class="mt-3 flex items-center space-x-4">
                        <a href="#" class="inline-flex items-center px-3 py-1.5 border border-amber-200 text-xs font-medium rounded-md text-amber-700 bg-amber-50 hover:bg-amber-100 focus:outline-none">
                            Review Request
                        </a>
                    </div>
                </div>
                 <!-- Unread Indicator -->
                 <div class="absolute bottom-4 right-4">
                    <div class="w-3 h-3 bg-amber-500 rounded-full border-2 border-white shadow-sm"></div>
                </div>
            </div>
        </div>

        {{-- ITEM 3: NORMAL (System Info) --}}
        <!-- DB Data: 
             type: 'system_info'
             priority: 'normal'
             is_read: true
             action_url: null
        -->
        <div class="bg-white border-l-4 border-blue-400 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow relative group opacity-75 hover:opacity-100">
            <div class="absolute top-3 right-3 flex items-center space-x-2">
                <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded uppercase tracking-wider">Normal</span>
                <span class="text-xs text-gray-400">Yesterday</span>
                <button class="text-gray-300 hover:text-chocolate"><i class="fas fa-ellipsis-v"></i></button>
            </div>
            
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-500">
                        <i class="fas fa-database"></i>
                    </div>
                </div>
                <div class="ml-4 flex-1 pr-20">
                    <h3 class="text-sm font-medium text-gray-900">Automated Backup Successful</h3>
                    <p class="text-sm text-gray-500 mt-1">The daily system backup was completed successfully at 03:00 AM. Size: 45MB.</p>
                </div>
            </div>
        </div>

        {{-- ITEM 4: NORMAL (Delivery Update) --}}
        <!-- DB Data: 
             type: 'delivery_update'
             priority: 'normal'
             is_read: true
             action_url: '/purchasing/po/history'
        -->
        <div class="bg-white border-l-4 border-green-500 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow relative group opacity-75 hover:opacity-100">
            <div class="absolute top-3 right-3 flex items-center space-x-2">
                <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded uppercase tracking-wider">Normal</span>
                <span class="text-xs text-gray-400">2 days ago</span>
                <button class="text-gray-300 hover:text-chocolate"><i class="fas fa-ellipsis-v"></i></button>
            </div>
            
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                        <i class="fas fa-truck"></i>
                    </div>
                </div>
                <div class="ml-4 flex-1 pr-20">
                    <h3 class="text-sm font-medium text-gray-900">Order Delivered: PO-2023-099</h3>
                    <p class="text-sm text-gray-500 mt-1">Items from Golden Grain Supplies have been received and stocked.</p>
                    
                    <div class="mt-3 flex items-center space-x-4">
                        <a href="#" class="text-xs text-green-600 hover:text-green-800 font-medium hover:underline">
                            View Receipt
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- 3. PAGINATION --}}
    <div class="bg-white px-4 py-3 flex items-center justify-between border border-gray-200 rounded-lg shadow-sm sm:px-6">
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <p class="text-sm text-gray-700">Showing <span class="font-medium">1</span> to <span class="font-medium">4</span> of <span class="font-medium">24</span> notifications</p>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Previous</button>
                <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-chocolate text-white text-sm font-medium">1</button>
                <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50">2</button>
                <button class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Next</button>
            </nav>
        </div>
    </div>

</div>
@endsection