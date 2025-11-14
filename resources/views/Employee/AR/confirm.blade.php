<div id="confirmAcknowledgementModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-md w-full rounded-lg border border-gray-200">
        <form method="POST" action="{{ route('employee.acknowledgements.confirm') }}">@csrf
            <input type="hidden" name="id" id="confirmAcknowledgementId">
            <div class="p-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Confirm Receipt</h3>
                <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-700 mb-2">Confirm receipt for <span id="confirmAcknowledgementRef" class="font-semibold"></span>?</p>
                <p class="text-xs text-gray-500">This will change the status to "received".</p>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white hover:bg-green-700 rounded">Confirm</button>
            </div>
        </form>
    </div>
</div>