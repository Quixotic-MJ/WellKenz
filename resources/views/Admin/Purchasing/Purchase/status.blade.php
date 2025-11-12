<div id="statusPurchaseOrderModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-md w-full rounded-lg border border-gray-200">
        <form id="statusPurchaseOrderForm" method="POST" action="">@csrf
            <div class="p-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Change Status</h3>
                <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-700">Update status for <span id="statusPORef" class="font-semibold"></span>?</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Status</label>
                    <select name="po_status" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                        <option value="draft">Draft</option>
                        <option value="ordered">Ordered</option>
                        <option value="delivered">Delivered</option>
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded">Update</button>
            </div>
        </form>
    </div>
</div>