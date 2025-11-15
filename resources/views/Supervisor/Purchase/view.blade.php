<div id="viewPOModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-5xl w-full rounded-lg border border-gray-200">
        <div class="p-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Purchase-Order Details</h3>
            <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- ajax fills --}}
        <div class="p-6 space-y-4 text-sm" id="viewPOBody">
            {{-- PO ref, supplier, linked req, items table, totals, delivery, memos, inventory txs --}}
        </div>

        {{-- supervisor quick actions --}}
        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
            <button onclick="openRejectModal()" class="px-3 py-1.5 text-sm border border-rose-300 text-rose-700 hover:bg-rose-50 rounded">
                <i class="fas fa-times mr-1"></i>Reject
            </button>
            <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">
                Close
            </button>
        </div>
    </div>
</div>