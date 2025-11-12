{{-- Activate Confirmation Modal --}}
<div id="activateModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-md w-full rounded-lg border border-gray-200">
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <h3 class="text-xl font-semibold text-gray-900">Activate Account</h3>
        </div>
        <div class="p-6">
            <p class="text-sm text-gray-700 mb-2">Are you sure you want to activate <span id="activateUserName" class="font-semibold"></span>?</p>
            <p class="text-xs text-gray-500">The user will regain system access with their existing permissions.</p>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
            <button onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">
                Cancel
            </button>
            <button onclick="toggleUserStatus()" class="px-4 py-2 bg-green-600 text-white hover:bg-green-700 rounded">
                Activate
            </button>
        </div>
    </div>
</div>