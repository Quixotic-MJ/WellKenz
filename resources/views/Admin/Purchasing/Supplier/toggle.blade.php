<div id="toggleSupplierModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-md w-full rounded-lg border border-gray-200">
        <form method="POST" action="">@csrf
            <div class="p-6 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">Toggle Status</h3>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-700 mb-2">Are you sure you want to <span id="toggleActionText" class="font-semibold"></span> <span id="toggleSupplierName" class="font-semibold"></span>?</p>
                <p class="text-xs text-gray-500">This will change the supplier status from <span id="toggleCurrentStatus" class="italic"></span>.</p>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-amber-600 text-white hover:bg-amber-700 rounded">Confirm</button>
            </div>
        </form>
    </div>
</div>