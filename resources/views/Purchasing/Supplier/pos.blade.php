<div id="posModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black bg-opacity-40" onclick="closeModals()"></div>
    <div class="relative z-10 bg-white w-full max-w-3xl rounded shadow-lg" onclick="event.stopPropagation()">
        <div class="px-5 py-3 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Supplier Purchase Orders</h3>
            <button onclick="closeModals()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <div id="posBody" class="p-5 text-sm text-gray-800">
            <!-- filled by JS -->
        </div>
        <div class="px-5 py-3 border-t bg-gray-50 text-right">
            <button onclick="closeModals()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded">Close</button>
        </div>
    </div>
</div>
