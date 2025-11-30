{{-- Add Supplier Modal --}}
<div class="fixed inset-0 bg-chocolate/20 backdrop-blur-sm overflow-y-auto h-full w-full z-50 hidden transition-opacity" id="addSupplierModal">
    <div class="relative top-8 mx-auto p-6 border border-border-soft max-w-2xl shadow-2xl rounded-xl bg-white transform transition-all scale-100 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-xl font-display font-bold text-chocolate">Add New Supplier</h3>
                <p class="text-sm text-gray-500 mt-1">Enter supplier information to create a new vendor record.</p>
            </div>
            <button onclick="closeAddSupplierModal()" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="addSupplierForm" onsubmit="submitAddSupplier(event)">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Supplier Name *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Enter supplier name">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Supplier Code *</label>
                    <input type="text" name="supplier_code" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Enter supplier code">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Contact Person</label>
                    <input type="text" name="contact_person" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Enter contact person">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Enter email address">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Phone</label>
                    <input type="text" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Enter phone number">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Mobile</label>
                    <input type="text" name="mobile" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Enter mobile number">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Payment Terms (Days)</label>
                    <input type="number" name="payment_terms" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="0">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Credit Limit</label>
                    <input type="number" name="credit_limit" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="0.00">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Rating</label>
                    <select name="rating" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all">
                        <option value="">No Rating</option>
                        <option value="1">1 Star</option>
                        <option value="2">2 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="5">5 Stars</option>
                    </select>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="supplier_is_active" value="1" checked class="rounded border-gray-300 text-chocolate focus:ring-chocolate w-4 h-4 mr-2">
                    <label for="supplier_is_active" class="text-sm font-bold text-gray-700">Active Supplier</label>
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Address</label>
                <textarea name="address" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Enter complete address"></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">City</label>
                    <input type="text" name="city" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="City">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Province</label>
                    <input type="text" name="province" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Province">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Postal Code</label>
                    <input type="text" name="postal_code" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Postal Code">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Tax ID</label>
                    <input type="text" name="tax_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Tax identification number">
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Notes</label>
                <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Additional notes about the supplier"></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeAddSupplierModal()" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark shadow-md transition-colors">
                    <i class="fas fa-plus mr-2"></i> Create Supplier
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Supplier Modal --}}
<div class="fixed inset-0 bg-chocolate/20 backdrop-blur-sm overflow-y-auto h-full w-full z-50 hidden transition-opacity" id="editSupplierModal">
    <div class="relative top-8 mx-auto p-6 border border-border-soft max-w-2xl shadow-2xl rounded-xl bg-white transform transition-all scale-100 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-xl font-display font-bold text-chocolate">Edit Supplier</h3>
                <p class="text-sm text-gray-500 mt-1">Update supplier information.</p>
            </div>
            <button onclick="closeEditSupplierModal()" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="editSupplierForm" onsubmit="submitEditSupplier(event)">
            @csrf
            @method('PUT')
            <input type="hidden" name="supplier_id" id="edit_supplier_id">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Supplier Name *</label>
                    <input type="text" name="name" id="edit_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Enter supplier name">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Supplier Code *</label>
                    <input type="text" name="supplier_code" id="edit_supplier_code" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Enter supplier code">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Contact Person</label>
                    <input type="text" name="contact_person" id="edit_contact_person" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Enter contact person">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" id="edit_email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Enter email address">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Phone</label>
                    <input type="text" name="phone" id="edit_phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Enter phone number">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Mobile</label>
                    <input type="text" name="mobile" id="edit_mobile" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Enter mobile number">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Payment Terms (Days)</label>
                    <input type="number" name="payment_terms" id="edit_payment_terms" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="0">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Credit Limit</label>
                    <input type="number" name="credit_limit" id="edit_credit_limit" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="0.00">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Rating</label>
                    <select name="rating" id="edit_rating" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all">
                        <option value="">No Rating</option>
                        <option value="1">1 Star</option>
                        <option value="2">2 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="5">5 Stars</option>
                    </select>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded border-gray-300 text-chocolate focus:ring-chocolate w-4 h-4 mr-2">
                    <label for="edit_is_active" class="text-sm font-bold text-gray-700">Active Supplier</label>
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Address</label>
                <textarea name="address" id="edit_address" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Enter complete address"></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">City</label>
                    <input type="text" name="city" id="edit_city" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="City">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Province</label>
                    <input type="text" name="province" id="edit_province" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Province">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Postal Code</label>
                    <input type="text" name="postal_code" id="edit_postal_code" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Postal Code">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Tax ID</label>
                    <input type="text" name="tax_id" id="edit_tax_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Tax identification number">
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Notes</label>
                <textarea name="notes" id="edit_notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" placeholder="Additional notes about the supplier"></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeEditSupplierModal()" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark shadow-md transition-colors">
                    <i class="fas fa-save mr-2"></i> Update Supplier
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Add Items Modal --}}
<div class="fixed inset-0 bg-chocolate/20 backdrop-blur-sm overflow-y-auto h-full w-full z-50 hidden transition-opacity" id="addItemsModal">
    <div class="relative top-8 mx-auto p-6 border border-border-soft max-w-4xl shadow-2xl rounded-xl bg-white transform transition-all scale-100 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-xl font-display font-bold text-chocolate">Add Items to Supplier</h3>
                <p class="text-sm text-gray-500 mt-1">Select items to add to this supplier's catalog.</p>
            </div>
            <button onclick="closeAddItemsModal()" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="mb-4">
            <div class="relative w-full max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 text-xs"></i>
                </div>
                <input type="text" id="itemSearch" onkeyup="filterAvailableItems()" 
                    class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" 
                    placeholder="Search items...">
            </div>
        </div>

        <div class="border border-gray-200 rounded-lg mb-6">
            <div class="max-h-64 overflow-y-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <input type="checkbox" id="selectAllAvailableItems" onchange="toggleSelectAllAvailableItems()" class="rounded border-gray-300 text-chocolate focus:ring-chocolate">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Item Details</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Unit</th>
                        </tr>
                    </thead>
                    <tbody id="availableItemsList" class="bg-white divide-y divide-gray-100">
                        {{-- Items will be loaded here via JavaScript --}}
                    </tbody>
                </table>
            </div>
        </div>

        <form id="addItemsForm" onsubmit="submitAddItems(event)">
            @csrf
            <input type="hidden" name="supplier_id" id="items_supplier_id">
            <div id="selectedItemsForm" class="mb-6 hidden">
                <h4 class="text-sm font-bold text-gray-700 mb-3">Configure Selected Items</h4>
                <div class="space-y-4" id="selectedItemsConfig">
                    {{-- Dynamic item configurations will be added here --}}
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeAddItemsModal()" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark shadow-md transition-colors" disabled id="addItemsSubmitBtn">
                    <i class="fas fa-plus mr-2"></i> Add Selected Items
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Supplier Item Modal --}}
<div class="fixed inset-0 bg-chocolate/20 backdrop-blur-sm overflow-y-auto h-full w-full z-50 hidden transition-opacity" id="editSupplierItemModal">
    <div class="relative top-20 mx-auto p-6 border border-border-soft max-w-md shadow-2xl rounded-xl bg-white transform transition-all scale-100">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-display font-bold text-chocolate">Edit Item Pricing</h3>
            <button onclick="closeEditSupplierItemModal()" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="editSupplierItemForm" onsubmit="submitEditSupplierItem(event)">
            @csrf
            @method('PATCH')
            <input type="hidden" name="supplier_item_id" id="edit_supplier_item_id">
            
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-2">Unit Price *</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">₱</span>
                    <input type="number" name="unit_price" id="edit_unit_price" step="0.01" min="0.01" required 
                           class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-2">Minimum Order Quantity *</label>
                <input type="number" name="minimum_order_quantity" id="edit_minimum_order_quantity" step="0.001" min="0.001" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-2">Lead Time (Days) *</label>
                <input type="number" name="lead_time_days" id="edit_lead_time_days" min="0" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all">
            </div>
            
            <div class="mb-6">
                <div class="flex items-center">
                    <input type="checkbox" name="is_preferred" id="edit_is_preferred" value="1" class="rounded border-gray-300 text-chocolate focus:ring-chocolate w-4 h-4 mr-2">
                    <label for="edit_is_preferred" class="text-sm font-bold text-gray-700">Preferred Supplier for this Item</label>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeEditSupplierItemModal()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark shadow-md transition-colors">
                    <i class="fas fa-save mr-1"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Confirm Remove Item Modal --}}
<div class="fixed inset-0 bg-chocolate/20 backdrop-blur-sm overflow-y-auto h-full w-full z-50 hidden transition-opacity" id="confirmRemoveItemModal">
    <div class="relative top-20 mx-auto p-6 border border-border-soft w-96 shadow-2xl rounded-xl bg-white transform transition-all scale-100">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 border border-red-200 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-display font-bold text-chocolate mb-2">Remove Item</h3>
            <p class="text-sm text-gray-500 mb-6 leading-relaxed" id="removeItemMessage">
                Are you sure you want to remove this item from the supplier?
            </p>
            
            <div class="flex justify-center gap-3">
                <button onclick="closeConfirmRemoveItemModal()" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="confirmRemoveItem()" class="px-5 py-2.5 bg-red-600 text-white text-sm font-bold rounded-lg hover:bg-red-700 shadow-md transition-colors">
                    <i class="fas fa-trash mr-1"></i> Remove
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Global Confirm Modal (for toggle status actions) --}}
<div class="fixed inset-0 bg-chocolate/20 backdrop-blur-sm overflow-y-auto h-full w-full z-50 hidden transition-opacity" id="globalConfirmModal">
    <div class="relative top-20 mx-auto p-6 border border-border-soft w-96 shadow-2xl rounded-xl bg-white transform transition-all scale-100">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-amber-100 border border-amber-200 mb-4">
                <i class="fas fa-question-circle text-amber-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-display font-bold text-chocolate mb-2" id="globalConfirmTitle">Confirm Action</h3>
            <p class="text-sm text-gray-500 mb-6 leading-relaxed" id="globalConfirmMessage">
                Are you sure you want to proceed with this action?
            </p>
            
            <div class="flex justify-center gap-3">
                <button onclick="closeGlobalConfirmModal()" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="confirmGlobalAction()" class="px-5 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark shadow-md transition-colors">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Toast Notifications --}}
<div id="toastContainer" class="fixed top-4 right-4 z-[60] space-y-2">
    {{-- Toast notifications will be dynamically added here --}}
</div>