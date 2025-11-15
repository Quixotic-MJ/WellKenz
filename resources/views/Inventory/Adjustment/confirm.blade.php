<div id="confirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-md w-full rounded-lg border border-gray-200">
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Confirm Adjustment</h3>
        </div>
        <div class="p-6 space-y-3 text-sm">
            <p class="text-gray-700">You are about to perform the following adjustment:</p>
            <div class="bg-gray-50 border border-gray-200 rounded p-3">
                <p><span class="font-semibold text-gray-900">Adjustment:</span> <span id="confirmAdj" class="font-mono"></span></p>
                <p><span class="font-semibold text-gray-900">Reason:</span> <span id="confirmReason"></span></p>
            </div>
            <p class="text-xs text-gray-500">This action will be logged and cannot be undone.</p>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
            <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</button>
            <button type="button" onclick="confirmSubmit()" class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 rounded">Confirm</button>
        </div>
    </div>
</div>