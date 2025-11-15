<div id="editItemModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-2xl w-full rounded-lg border border-gray-200">
        <form method="POST" id="editItemForm">@csrf @method('PUT')
            <div class="p-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Edit Item</h3>
                <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6 space-y-4 text-sm" id="editItemBody">
                {{-- ajax populates: name, description, unit, category, expiry, reorder, min/max --}}
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 rounded">Save Changes</button>
            </div>
        </form>
    </div>
</div>
<script>
function openEditModal(id){
    currentId=id;
    document.getElementById('editItemForm').action=`/inventory/items/${id}`;
    /* ajax fetch then fill fields */
    document.getElementById('editItemModal').classList.remove('hidden');
}
</script>