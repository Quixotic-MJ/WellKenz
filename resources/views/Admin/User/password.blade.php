{{-- Reset Password Modal --}}
<div id="passwordModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-md w-full rounded-lg border border-gray-200">
        <form id="changePasswordForm" method="POST" action="{{ url('/admin/users/0/password') }}">
            @csrf @method('PUT')
            <input type="hidden" name="user_id" id="change_password_user_id">
            <div class="p-6 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900">Reset Password</h3>
                    <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-700">Set a new password for <span id="changePasswordUsername" class="font-semibold"></span></p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input type="password" name="password" required
                           class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeModals()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-amber-600 text-white hover:bg-amber-700 rounded">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>