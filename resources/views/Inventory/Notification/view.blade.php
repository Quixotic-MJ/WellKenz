<div id="viewNotificationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-2xl w-full rounded-lg border border-gray-200">
        <div class="p-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Notification Details</h3>
            <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-6 space-y-3 text-sm">
            <div>
                <span class="text-xs text-gray-500">Module</span>
                <p id="viewNotifModule" class="font-semibold text-gray-900"></p>
            </div>
            <div>
                <span class="text-xs text-gray-500">Title</span>
                <p id="viewNotifTitle" class="font-semibold text-gray-900"></p>
            </div>
            <div>
                <span class="text-xs text-gray-500">Message</span>
                <p id="viewNotifContent" class="text-gray-700 whitespace-pre-wrap"></p>
            </div>
            <div>
                <span class="text-xs text-gray-500">Received</span>
                <p id="viewNotifDate" class="text-gray-700"></p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-between items-center">
            <a id="viewNotifLink" href="#" class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 rounded-lg text-sm">
                <i class="fas fa-external-link-alt mr-2"></i>Open Record
            </a>
            <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg text-sm">Close</button>
        </div>
    </div>
</div>