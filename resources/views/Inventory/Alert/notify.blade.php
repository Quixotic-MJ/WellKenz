<div id="notifyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-xl w-full rounded-lg border border-gray-200">
        <form method="POST" action="{{ route('inventory.low-stock.notify') }}">@csrf
            <div class="p-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Notify Purchasing</h3>
                <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6 space-y-4 text-sm">
                <p class="text-gray-600">Send a summary of current low-stock items to the purchasing team.</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Additional Message (optional)</label>
                    <textarea name="message" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400"></textarea>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-amber-600 text-white hover:bg-amber-700 rounded">Send Notification</button>
            </div>
        </form>
    </div>
</div>