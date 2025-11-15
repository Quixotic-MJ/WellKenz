<div id="viewNotificationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-2xl w-full rounded-lg border border-gray-200">
        <div class="p-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Notification Details</h3>
            <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>

        <div class="p-6 space-y-3 text-sm" id="viewNotificationBody">
            {{-- ajax populates: title, content, module, related_id, timestamp, link button --}}
        </div>

        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
            <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Close</button>
        </div>
    </div>
</div>