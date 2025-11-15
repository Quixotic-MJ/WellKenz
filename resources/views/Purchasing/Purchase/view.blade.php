<div id="viewReqModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-50 transition-opacity">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-auto" role="dialog" aria-modal="true" aria-labelledby="viewReqTitle">
            <div class="p-6 border-b flex justify-between items-center">
                <h3 id="viewReqTitle" class="text-xl font-semibold text-gray-800">Requisition Details (View Only)</h3>
                <button onclick="closeModals()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 max-h-[70vh] overflow-y-auto" id="viewReqModalBody">
                </div>
            <div class="p-4 border-t flex justify-end" id="viewReqModalFooter">
                <button onclick="closeModals()" type="button" class="px-4 py-2 bg-gray-200 text-gray-700 hover:bg-gray-300 transition text-sm font-medium rounded">Close</button>
            </div>
        </div>
    </div>
</div>