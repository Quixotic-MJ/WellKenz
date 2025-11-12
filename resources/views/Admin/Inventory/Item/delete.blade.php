<div id="deleteItemModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-md w-full rounded-lg border border-gray-200">
        <form method="POST" action="{{ route('items.destroy',0) }}" id="deleteItemForm">@csrf @method('DELETE')
            <div class="p-6 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">Delete Item</h3>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-700 mb-2">Are you sure you want to delete <span id="deleteItemName" class="font-semibold"></span>?</p>
                <p class="text-xs text-red-600">This action cannot be undone if no transactions exist.</p>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-800 text-white hover:bg-red-900 rounded">Delete</button>
            </div>
        </form>
    </div>
</div>