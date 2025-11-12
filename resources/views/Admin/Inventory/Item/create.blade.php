<div id="createItemModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-2xl w-full rounded-lg border border-gray-200">
        <form method="POST" action="{{ route('items.store') }}">@csrf
            <div class="p-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">New Item</h3>
                <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6 grid grid-cols-2 gap-4 text-sm">
                <div class="col-span-2"><label class="block font-medium text-gray-700 mb-1">Item Name</label><input type="text" name="item_name" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400"></div>
                <div><label class="block font-medium text-gray-700 mb-1">Category</label>
                    <select name="category_id" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                        @foreach($categories as $cat)<option value="{{ $cat->category_id }}">{{ $cat->category_name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block font-medium text-gray-700 mb-1">Unit</label><input type="text" name="unit" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400"></div>
                <div><label class="block font-medium text-gray-700 mb-1">Current Stock</label><input type="number" name="current_stock" required min="0" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400"></div>
                <div><label class="block font-medium text-gray-700 mb-1">Reorder Level</label><input type="number" name="reorder_level" required min="0" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400"></div>
                <div><label class="block font-medium text-gray-700 mb-1">Expiry Date</label><input type="date" name="expiry_date" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400"></div>
                <div class="col-span-2"><label class="block font-medium text-gray-700 mb-1">Description</label><textarea name="description" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400"></textarea></div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 rounded">Save</button>
            </div>
        </form>
    </div>
</div>