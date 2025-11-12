{{-- Deactivate Confirmation Modal --}}
<div id="deactivateModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-md w-full rounded-lg border border-gray-200">
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <h3 class="text-xl font-semibold text-gray-900">Deactivate Account</h3>
        </div>
        <div class="p-6">
            <p class="text-sm text-gray-700 mb-2">Are you sure you want to deactivate <span id="deactivateUserName" class="font-semibold"></span>?</p>
            <p class="text-xs text-gray-500">The user will lose system access but the account remains in the database.</p>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
            <button onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">
                Cancel
            </button>
            <button onclick="toggleUserStatus()" class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded">
                Deactivate
            </button>
        </div>
    </div>
</div>