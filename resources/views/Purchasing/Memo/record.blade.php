<div id="recordDeliveryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-3xl w-full rounded-lg border border-gray-200">
        <form method="POST" id="deliveryForm">@csrf
            <div class="p-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Record Delivery</h3>
                <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6 space-y-4 text-sm" id="deliveryBody">
                {{-- ajax populates: PO ref, supplier, items list, hidden po_id --}}
                <input type="hidden" name="po_id" id="poIdInput">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Memo Reference <span class="text-rose-500">*</span></label>
                        <input type="text" name="memo_ref" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Received Date <span class="text-rose-500">*</span></label>
                        <input type="date" name="received_date" value="{{ today()->toDateString() }}" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Received By <span class="text-rose-500">*</span></label>
                    <input type="text" name="received_by" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                    <textarea name="remarks" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Delivered Items</label>
                    <table class="w-full text-sm border border-gray-200 rounded">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left">Item</th>
                                <th class="px-3 py-2 text-left">Unit</th>
                                <th class="px-3 py-2 text-right">Ordered</th>
                                <th class="px-3 py-2 text-right">Delivered</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white hover:bg-green-700 rounded">Confirm Delivery</button>
            </div>
        </form>
    </div>
</div>

<script>
/* populate modal & handle submit */
document.getElementById('deliveryForm').addEventListener('submit', function(e){
    e.preventDefault();
    fetch(`/purchasing/delivery/${currentPOId}`,{
        method:'POST',
        headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body: new FormData(this)
    })
    .then(r => r.ok ? r.json() : Promise.reject(r))
    .then(res => {
        if(res.success){
            showMessage(res.message);
            setTimeout(()=> location.reload(), 500);
        }else{
            showMessage(res.message || 'Save failed','error');
        }
    })
    .catch(() => showMessage('Server error','error'));
});
</script>