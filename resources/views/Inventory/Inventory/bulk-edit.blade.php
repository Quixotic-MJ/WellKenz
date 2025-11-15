<div id="bulkEditModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-2xl w-full rounded-lg border border-gray-200">
        <form method="POST" action="{{ route('inventory.items.bulk-update') }}" id="bulkEditForm">@csrf
            <div class="p-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Bulk Edit Selected Items</h3>
                <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6 space-y-4 text-sm" id="bulkEditBody">
                <p class="text-gray-600">Only tick the fields you want to update for all selected items.</p>
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" name="update[]" value="reorder_level" class="mr-2">
                        <span class="text-sm text-gray-700">Reorder Level</span>
                        <input type="number" name="reorder_level" min="0" class="ml-auto w-24 border border-gray-300 rounded px-2 py-1">
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="update[]" value="min_stock_level" class="mr-2">
                        <span class="text-sm text-gray-700">Minimum Stock</span>
                        <input type="number" name="min_stock_level" min="0" class="ml-auto w-24 border border-gray-300 rounded px-2 py-1">
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="update[]" value="maximum_stock" class="mr-2">
                        <span class="text-sm text-gray-700">Maximum Stock</span>
                        <input type="number" name="maximum_stock" min="0" class="ml-auto w-24 border border-gray-300 rounded px-2 py-1">
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="update[]" value="item_expire_date" class="mr-2">
                        <span class="text-sm text-gray-700">Expiry Date</span>
                        <input type="date" name="item_expire_date" class="ml-auto border border-gray-300 rounded px-2 py-1">
                    </label>
                </div>
                <input type="hidden" name="item_ids" id="selectedItemIds" value="">
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 rounded">Update Selected</button>
            </div>
        </form>
    </div>
</div>
<script>
function openBulkEditModal(){
    const checked = document.querySelectorAll('.row-check:checked');
    if(checked.length === 0){ alert('No items selected'); return; }
    const ids = [...checked].map(chk => chk.value);
    document.getElementById('selectedItemIds').value = ids.join(',');
    document.getElementById('bulkEditModal').classList.remove('hidden');
}
</script>