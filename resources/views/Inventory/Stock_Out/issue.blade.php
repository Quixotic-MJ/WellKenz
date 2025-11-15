<div id="issueItemsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-5xl w-full rounded-lg border border-gray-200">
        <form method="POST" id="issueForm">@csrf
            <div class="p-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Issue Items / Create AR</h3>
                <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6 space-y-4 text-sm" id="issueBody">
                {{-- ajax populates: hidden req_id, requisition details, items table, staff, ref, date --}}
                <input type="hidden" name="req_id" id="reqIdInput">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">AR Reference <span class="text-rose-500">*</span></label>
                        <input type="text" name="ar_ref" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Issue Date <span class="text-rose-500">*</span></label>
                        <input type="date" name="issued_date" value="{{ today()->toDateString() }}" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Issued By <span class="text-rose-500">*</span></label>
                    <input type="text" name="issued_by" value="{{ session('emp_name') }}" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Items to Issue</label>
                    <table class="w-full text-sm border border-gray-200 rounded">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left">Item</th>
                                <th class="px-3 py-2 text-left">Unit</th>
                                <th class="px-3 py-2 text-right">Requested</th>
                                <th class="px-3 py-2 text-right">Available</th>
                                <th class="px-3 py-2 text-right">Issue Qty <span class="text-rose-500">*</span></th>
                                <th class="px-3 py-2 text-right">Balance After</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                    </table>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Remarks / Pick-up Notes</label>
                    <textarea name="remarks" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400"></textarea>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded">Confirm Issue</button>
            </div>
        </form>
    </div>
</div>

<script>
/* populate modal & handle submit */
document.getElementById('issueForm').addEventListener('submit', function(e){
    e.preventDefault();
    fetch(`/inventory/stock-out/${currentReqId}`,{
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

/* line calculation & balance preview */
function calcIssue(el){
    const row   = el.closest('tr');
    const req   = parseFloat(row.dataset.requested) || 0;
    const avail = parseFloat(row.dataset.available) || 0;
    const issue = parseFloat(el.value) || 0;
    const after = avail - issue;
    row.querySelector('.balance-after').textContent = after;
    if(issue > avail){
        el.setCustomValidity('Issue qty exceeds available stock');
    }else if(issue > req){
        el.setCustomValidity('Issue qty exceeds requested qty');
    }else{
        el.setCustomValidity('');
    }
}
</script>