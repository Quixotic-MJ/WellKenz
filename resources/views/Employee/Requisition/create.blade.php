<div id="createRequisitionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-3xl w-full rounded-lg border border-gray-200">
        <form method="POST" action="{{ route('staff.requisitions.store') }}" id="createReqForm">@csrf
            <div class="p-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">New Requisition</h3>
                <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6 space-y-4 text-sm">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Purpose <span class="text-rose-500">*</span></label>
                        <input type="text" name="req_purpose" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority <span class="text-rose-500">*</span></label>
                        <select name="req_priority" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Items <span class="text-rose-500">*</span></label>
                    <div class="border border-gray-200 rounded">
                        <table class="w-full text-sm" id="itemsTable">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-3 py-2 text-left">Item</th>
                                    <th class="px-3 py-2 text-left">Unit</th>
                                    <th class="px-3 py-2 text-left">Stock</th>
                                    <th class="px-3 py-2 text-left">Qty</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsTbody">
                                <tr>
                                    <td class="px-3 py-2">
                                        <select name="items[0][item_id]" required class="item-select w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                                            <option value="">Choose item…</option>
                                            @foreach($items as $i)<option value="{{ $i->item_id }}" data-unit="{{ $i->item_unit }}" data-stock="{{ $i->item_stock }}">{{ $i->item_name }}</option>@endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 unit-cell text-gray-600">-</td>
                                    <td class="px-3 py-2 stock-cell text-gray-600">-</td>
                                    <td class="px-3 py-2"><input type="number" name="items[0][quantity]" min="1" required class="qty-input w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"></td>
                                    <td class="px-3 py-2"><button type="button" onclick="removeRow(this)" class="text-rose-600 hover:text-rose-800"><i class="fas fa-trash text-xs"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="px-3 py-2 border-t border-gray-200 bg-gray-50">
                            <button type="button" onclick="addRow()" class="text-sm text-blue-600 hover:underline"><i class="fas fa-plus mr-1"></i>Add row</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 rounded">Submit</button>
            </div>
        </form>
    </div>
</div>

<script>
let rowIdx=1;
function addRow(){
    const tbody=document.getElementById('itemsTbody');
    const tr=document.createElement('tr');
    tr.innerHTML=`
        <td class="px-3 py-2">
            <select name="items[${rowIdx}][item_id]" required class="item-select w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                <option value="">Choose item…</option>
                @foreach($items as $i)<option value="{{ $i->item_id }}" data-unit="{{ $i->item_unit }}" data-stock="{{ $i->item_stock }}">{{ $i->item_name }}</option>@endforeach
            </select>
        </td>
        <td class="px-3 py-2 unit-cell text-gray-600">-</td>
        <td class="px-3 py-2 stock-cell text-gray-600">-</td>
        <td class="px-3 py-2"><input type="number" name="items[${rowIdx}][quantity]" min="1" required class="qty-input w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"></td>
        <td class="px-3 py-2"><button type="button" onclick="removeRow(this)" class="text-rose-600 hover:text-rose-800"><i class="fas fa-trash text-xs"></i></button></td>
    `;
    tbody.appendChild(tr); rowIdx++;
    attachSelectListeners();
}
function removeRow(btn){ btn.closest('tr').remove(); }
function attachSelectListeners(){
    document.querySelectorAll('.item-select').forEach(s=>{
        s.onchange=function(){
            const opt=this.options[this.selectedIndex];
            const row=this.closest('tr');
            row.querySelector('.unit-cell').textContent=opt.dataset.unit||'-';
            row.querySelector('.stock-cell').textContent=opt.dataset.stock||'-';
        };
    });
}
attachSelectListeners();

document.getElementById('createReqForm').addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(data => {
        if(data.success){
            showMessage(data.message);
            closeModals();
            setTimeout(() => location.reload(), 1000);
        } else {
            showMessage(data.message || 'Error', 'error');
        }
    })
    .catch(() => showMessage('Error submitting requisition', 'error'));
});
</script>