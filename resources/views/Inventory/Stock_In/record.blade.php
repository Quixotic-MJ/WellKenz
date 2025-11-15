<div id="recordStockInModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-4xl w-full rounded-lg border border-gray-200">
        <form method="POST" id="stockInForm">@csrf
            <div class="p-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Record Stock-In</h3>
                <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6 space-y-4 text-sm" id="stockInBody">
                {{-- ajax populates: hidden po_id, items table, staff, ref, date --}}
                <input type="hidden" name="po_id" id="poIdInput">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock-In Reference <span class="text-rose-500">*</span></label>
                        <input type="text" name="trans_ref" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Date <span class="text-rose-500">*</span></label>
                        <input type="date" name="trans_date" value="{{ today()->toDateString() }}" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Received By <span class="text-rose-500">*</span></label>
                    <input type="text" name="received_by" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Items to Receive</label>
                    <table class="w-full text-sm border border-gray-200 rounded">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left">Item</th>
                                <th class="px-3 py-2 text-left">Unit</th>
                                <th class="px-3 py-2 text-right">Ordered</th>
                                <th class="px-3 py-2 text-right">Qty In <span class="text-rose-500">*</span></th>
                                <th class="px-3 py-2 text-right">Unit Cost (₱)</th>
                                <th class="px-3 py-2 text-right">Line Total</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                        <tfoot>
                            <tr class="border-t-2">
                                <td colspan="5" class="px-3 py-2 text-right font-semibold text-gray-900">Total</td>
                                <td class="px-3 py-2 text-right font-semibold text-gray-900" id="grandTotal">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                    <textarea name="remarks" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400"></textarea>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white hover:bg-green-700 rounded">Confirm Stock-In</button>
            </div>
        </form>
    </div>
</div>

<script>
/* populate modal & handle submit */
document.getElementById('stockInForm').addEventListener('submit', function(e){
    e.preventDefault();
    fetch(`/inventory/stock-in/${currentPOId}`,{
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

/* line calculation */
function calcLine(el){
    const row   = el.closest('tr');
    const qty   = parseFloat(row.querySelector('.qty-in').value) || 0;
    const price = parseFloat(row.querySelector('.unit-cost').value) || 0;
    const line  = (qty * price).toFixed(2);
    row.querySelector('.line-total').textContent = line;
    grandTotal();
}
function grandTotal(){
    let gt=0;
    document.querySelectorAll('.line-total').forEach(td=> gt+=parseFloat(td.textContent)||0);
    document.getElementById('grandTotal').textContent = gt.toFixed(2);
}
</script>