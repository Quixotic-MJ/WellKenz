<div id="editSupModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black bg-opacity-40" onclick="closeModals()"></div>
    <div class="relative z-10 bg-white w-full max-w-lg rounded shadow-lg" onclick="event.stopPropagation()">
        <div class="px-5 py-3 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Edit Supplier</h3>
            <button onclick="closeModals()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <form id="editSupForm" class="p-5 space-y-3">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <div>
                <label class="text-xs text-gray-500 uppercase">Name</label>
                <input type="text" name="sup_name" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-sm" required />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs text-gray-500 uppercase">Email</label>
                    <input type="email" name="sup_email" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase">Phone</label>
                    <input type="text" name="contact_number" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-sm" />
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs text-gray-500 uppercase">Contact Person</label>
                    <input type="text" name="contact_person" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase">Status</label>
                    <input type="text" name="sup_status" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-sm" disabled />
                </div>
            </div>
            <div>
                <label class="text-xs text-gray-500 uppercase">Address</label>
                <textarea name="sup_address" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-sm" rows="2"></textarea>
            </div>
        </form>
        <div class="px-5 py-3 border-t bg-gray-50 text-right">
            <button onclick="closeModals()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded mr-2">Cancel</button>
            <button id="editSupSaveBtn" class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
        </div>
    </div>
</div>
