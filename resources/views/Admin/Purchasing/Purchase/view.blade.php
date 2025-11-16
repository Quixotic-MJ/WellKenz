<div id="viewPurchaseOrderModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-4xl w-full max-h-[90vh] overflow-y-auto rounded-lg border border-gray-200">
        <div class="p-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Purchase Order Items</h3>
            <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-6">
            <table class="w-full text-sm" id="viewPOItemsTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Item</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Qty</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Unit</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Price (₱)</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Subtotal (₱)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="viewPOItemsBody">
                    {{-- ajax populates rows --}}
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
            <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Close</button>
        </div>
    </div>
</div>