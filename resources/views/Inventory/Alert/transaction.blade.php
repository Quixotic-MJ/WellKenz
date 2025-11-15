<div id="transactionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-2xl w-full rounded-lg border border-gray-200">
        <div class="p-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Stock Transaction</h3>
            <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-6 space-y-4 text-sm" id="transactionBody">
            {{-- ajax populates: item info, form for in/out, qty, ref, remarks --}}
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
            <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white hover:bg-green-700 rounded">Save</button>
        </div>
    </div>
</div>