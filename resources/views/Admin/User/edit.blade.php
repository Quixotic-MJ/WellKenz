{{-- Edit User Modal --}}
<div id="editUserModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-2xl w-full max-h-[90vh] overflow-y-auto rounded-lg border border-gray-200">
        <form id="editUserForm" method="POST" action="">
            @csrf @method('PUT')
            <input type="hidden" name="user_id" id="edit_user_id">
            <div class="p-6 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900">Edit Account</h3>
                    <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" name="username" id="edit_username" required
                               class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select name="role" id="edit_role" required
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                            <option value="admin">Admin</option>
                            <option value="employee">Employee</option>
                            <option value="inventory">Inventory Staff</option>
                            <option value="purchasing">Purchase Staff</option>
                            <option value="supervisor">Supervisor</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" id="edit_name" required
                               class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                        <input type="text" name="position" id="edit_position" required
                               class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="edit_email" required
                               class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact</label>
                        <input type="text" name="contact" id="edit_contact" required
                               class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeModals()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 rounded">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>