<div id="rejectItemRequestModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-md w-full rounded-lg border border-gray-200">
        <form method="POST" action="">@csrf
            <input type="hidden" name="item_req_status" value="rejected">
            <div class="p-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Reject Item Request</h3>
                <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-700">Reject request for <span id="rejectItemRequestName" class="font-semibold"></span>?</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason (stored in item_req_reject_reason)</label>
                    <textarea name="item_req_reject_reason" rows="4" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400"></textarea>
                </div>
                <p class="text-xs text-gray-500">The requester will be notified immediately.</p>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-rose-600 text-white hover:bg-rose-700 rounded">Reject</button>
            </div>
        </form>
    </div>
</div>