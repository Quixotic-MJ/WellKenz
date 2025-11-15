<div id="approvePOModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-xl w-full rounded-lg border border-gray-200">
        <form method="POST" action="{{ route('supervisor.po.approve', 0) }}" id="approvePOForm">
            @csrf @method('PUT')

            <div class="p-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">High-Value Approval</h3>
                <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 text-sm">
                <p class="text-gray-600">
                    This PO exceeds the supervisor-approval threshold.  Please confirm or comment.
                </p>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="action" value="approve" class="mr-2" required>
                            <span class="text-sm">Approve</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="action" value="reject" class="mr-2" required>
                            <span class="text-sm">Reject</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Comment / Reason</label>
                    <textarea name="comment" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400"></textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 rounded">Confirm</button>
            </div>
        </form>
    </div>
</div>

<script>
/* when modal opens, set correct action URL */
function openApproveModal(id){
    currentId = id;
    const form = document.getElementById('approvePOForm');
    form.action = `/supervisor/purchase-orders/${id}/approve`;
    document.getElementById('approvePOModal').classList.remove('hidden');
}
</script>