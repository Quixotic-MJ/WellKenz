<div id="viewNotificationModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black bg-opacity-40" onclick="closeModals()"></div>
    <div class="relative z-10 bg-white w-full max-w-lg rounded shadow-lg" onclick="event.stopPropagation()">
        <div class="px-5 py-3 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Notification Details</h3>
            <button onclick="closeModals()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-5 space-y-3">
            <div>
                <label class="text-xs text-gray-500 uppercase">Title</label>
                <p id="viewNotifTitle" class="text-sm font-semibold text-gray-900 mt-1">-</p>
            </div>
            <div>
                <label class="text-xs text-gray-500 uppercase">Module</label>
                <p id="viewNotifModule" class="text-sm text-gray-900 mt-1">-</p>
            </div>
            <div>
                <label class="text-xs text-gray-500 uppercase">Content</label>
                <p id="viewNotifContent" class="text-sm text-gray-900 mt-1">-</p>
            </div>
            <div>
                <label class="text-xs text-gray-500 uppercase">Date</label>
                <p id="viewNotifDate" class="text-sm text-gray-900 mt-1">-</p>
            </div>
        </div>
        <div class="px-5 py-3 border-t bg-gray-50 flex justify-between">
            <a id="viewNotifLink" href="#" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                <i class="fas fa-external-link-alt mr-2"></i>Open Record
            </a>
            <button onclick="closeModals()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded">Close</button>
        </div>
    </div>
</div>