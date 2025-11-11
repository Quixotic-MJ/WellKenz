@extends('Employee.layout.app')

@section('title', 'Create Requisition - WellKenz ERP')
@section('breadcrumb', 'Create Requisition')

@section('content')
<div class="space-y-6">
    <!-- Messages -->
    <div id="successMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"   class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Create New Requisition</h1>
        <p class="text-gray-600">Request items from inventory for your needs</p>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- LEFT: Form + Items -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Requisition Details Card -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Requisition Details</h3>
                <form id="requisitionForm" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Requisition Reference</label>
                            <input type="text" id="req_ref_display" readonly placeholder="Will be generated on submit" class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-2 text-gray-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priority Level</label>
                            <select name="req_priority" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                                <option value="">Select Priority</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Requested By</label>
                            <input type="text" id="requested_by" readonly class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-2 text-gray-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <input type="text" id="current_date" readonly class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-2 text-gray-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Purpose / Remarks</label>
                        <textarea name="req_purpose" rows="3" required placeholder="Enter the purpose of this requisition" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400"></textarea>
                    </div>
                </form>
            </div>

            <!-- Requisition Items Card -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Requisition Items</h3>
                    <button type="button" onclick="openItemModal()" class="px-4 py-2 bg-gray-800 text-white hover:bg-gray-700 text-sm font-medium rounded">
                        <i class="fas fa-plus mr-2"></i>Add Item
                    </button>
                </div>

                <!-- Selected Items Table -->
                <div class="overflow-x-auto">
                    <table class="w-full" id="selectedItemsTable">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Item</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Qty</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Unit</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200" id="selectedItemsBody">
                            <tr id="noItemsRow">
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                    <i class="fas fa-box-open text-2xl mb-2 opacity-50"></i>
                                    <p>No items added yet. Click "Add Item" to get started.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-4">
                    <button type="button" id="submitRequisitionBtn" onclick="submitRequisition()" class="px-6 py-2 bg-gray-800 text-white hover:bg-gray-700 text-sm font-medium rounded">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Requisition
                    </button>
                </div>
            </div>
        </div>

        <!-- RIGHT: Sidebar -->
        <div class="space-y-6">
            <!-- Summary -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Requisition Summary</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium text-gray-700">Total Items</span>
                        <span id="totalItemsCount" class="text-lg font-semibold text-gray-800">0</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium text-gray-700">Priority</span>
                        <span id="selectedPriority" class="text-sm font-medium text-gray-600">Not set</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium text-gray-700">Requester</span>
                        <span id="requesterName" class="text-sm font-medium text-gray-800">{{ Auth::user()->name }}</span>
                    </div>
                </div>
            </div>

            <!-- Guidelines -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Guidelines</h3>
                <div class="space-y-3">
                    <div class="flex items-start gap-2"><i class="fas fa-info text-blue-500 mt-1"></i><p class="text-sm text-gray-600">Provide clear purpose for better approval</p></div>
                    <div class="flex items-start gap-2"><i class="fas fa-exclamation-triangle text-amber-500 mt-1"></i><p class="text-sm text-gray-600">High priority requests are reviewed first</p></div>
                    <div class="flex items-start gap-2"><i class="fas fa-box text-gray-500 mt-1"></i><p class="text-sm text-gray-600">Check stock availability before requesting</p></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div id="itemModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-2xl w-full max-h-[90vh] overflow-y-auto rounded-lg">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-xl font-semibold text-gray-800">Select Items from Inventory</h3>
            <button onclick="closeItemModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-4">
            <!-- Search Bar -->
            <div class="mb-4 relative">
                <input type="text" id="itemSearch" placeholder="Search items..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400" onkeyup="searchItems(this.value)">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>

            <!-- Inventory Items Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4" id="inventoryItemsGrid">
                <div class="col-span-2 text-center py-8"><i class="fas fa-spinner fa-spin text-2xl mb-3 opacity-50"></i><p class="text-gray-500">Loading items...</p></div>
            </div>

            <!-- Selected Item Details -->
            <div id="selectedItemDetails" class="hidden bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-800 mb-3">Add Item to Requisition</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item Name</label>
                        <input type="text" id="selectedItemName" readonly class="w-full bg-gray-100 border border-gray-300 rounded px-3 py-2 text-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Available Stock</label>
                        <input type="text" id="selectedItemStock" readonly class="w-full bg-gray-100 border border-gray-300 rounded px-3 py-2 text-gray-700">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity Requested</label>
                        <input type="number" id="itemQuantity" min="1" value="1" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400" onchange="validateQuantity(this)">
                        <p id="quantityWarning" class="text-xs text-amber-600 mt-1 hidden">Requested quantity exceeds available stock</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                        <input type="text" id="selectedItemUnit" readonly class="w-full bg-gray-100 border border-gray-300 rounded px-3 py-2 text-gray-700">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-4">
                    <button type="button" onclick="closeItemDetails()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</button>
                    <button type="button" onclick="addItemToRequisition()" id="addToRequisitionBtn" class="px-4 py-2 bg-gray-800 text-white hover:bg-gray-700 rounded">Add to Requisition</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/* ----------  GLOBALS  ---------- */
let selectedItems = [];
let currentSelectedItem = null;
let inventoryItems = [];

/* ----------  INIT  ---------- */
document.addEventListener('DOMContentLoaded', () => {
    initialisePage();
    setupEventListeners();
    loadInventoryItems();
});

function initialisePage(){
    const now = new Date();
    document.getElementById('current_date').value = now.toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'});
    document.getElementById('requested_by').value = '{{ Auth::user()->name }}';
    document.getElementById('requesterName').textContent = '{{ Auth::user()->name }}';
    // server will generate req_ref on submit
    updateSummary();
    setReqRefDisplay('Will be generated on submit');
}
function setReqRefDisplay(ref){ document.getElementById('req_ref_display').value = ref || 'Will be generated on submit'; }
function generateRequisitionReference(){
    // removed
}
function setupEventListeners(){
    const prioritySelect=document.querySelector('select[name="req_priority"]');
    if(prioritySelect){
        prioritySelect.addEventListener('change',function(){
            document.getElementById('selectedPriority').textContent=this.value?this.value.charAt(0).toUpperCase()+this.value.slice(1):'Not set';
        });
    }
}

/* ----------  MODAL  ---------- */
function openItemModal(){document.getElementById('itemModal').classList.remove('hidden');}
function closeItemModal(){document.getElementById('itemModal').classList.add('hidden');closeItemDetails();}

/* ----------  ITEM SEARCH  ---------- */
function searchItems(query){
    const items=document.querySelectorAll('.inventory-item');
    const q=query.toLowerCase();
    items.forEach(el=>{
        const name=el.getAttribute('data-item-name').toLowerCase();
        const code=el.getAttribute('data-item-code').toLowerCase();
        const cat=el.getAttribute('data-category').toLowerCase();
        el.style.display=(name.includes(q)||code.includes(q)||cat.includes(q))?'block':'none';
    });
}

/* ----------  SELECT / QUICK ADD  ---------- */
function handleSelectItem(btn){
    const el=btn.closest('.inventory-item');
    currentSelectedItem={
        id:parseInt(el.getAttribute('data-item-id')),
        name:el.getAttribute('data-item-name'),
        code:el.getAttribute('data-item-code'),
        stock:parseFloat(el.getAttribute('data-stock')),
        unit:el.getAttribute('data-unit'),
        category:el.getAttribute('data-category'),
        is_custom:el.getAttribute('data-is-custom')==='1'        // ← NEW
    };
    document.getElementById('selectedItemName').value=currentSelectedItem.name;
    document.getElementById('selectedItemStock').value=`${currentSelectedItem.stock} ${currentSelectedItem.unit}`;
    document.getElementById('selectedItemUnit').value=currentSelectedItem.unit;
    document.getElementById('itemQuantity').value=1;
    document.getElementById('quantityWarning').classList.add('hidden');
    document.getElementById('selectedItemDetails').classList.remove('hidden');
    setTimeout(()=>document.getElementById('selectedItemDetails').scrollIntoView({behavior:'smooth',block:'nearest'}),100);
}
function closeItemDetails(){
    document.getElementById('selectedItemDetails').classList.add('hidden');
    currentSelectedItem=null;
}
function quickAddItem(btn){
    const el=btn.closest('.inventory-item');
    const item={
        id:parseInt(el.getAttribute('data-item-id')),
        name:el.getAttribute('data-item-name'),
        code:el.getAttribute('data-item-code'),
        stock:parseFloat(el.getAttribute('data-stock')),
        unit:el.getAttribute('data-unit'),
        category:el.getAttribute('data-category'),
        is_custom:el.getAttribute('data-is-custom')==='1'        // ← NEW
    };
    const idx=selectedItems.findIndex(i=>parseInt(i.id)===item.id);
    if(idx>-1){selectedItems[idx].quantity+=1;showMessage('Item quantity increased!','success');}
    else{selectedItems.push({...item,quantity:1});showMessage('Item added to requisition!','success');}
    updateItemsTable();
}
function validateQuantity(inp){
    const qty=parseFloat(inp.value);
    const warn=document.getElementById('quantityWarning');
    qty>currentSelectedItem.stock?warn.classList.remove('hidden'):warn.classList.add('hidden');
}
function addItemToRequisition(){
    if(!currentSelectedItem){showMessage('Please select an item first','error');return;}
    const qty=parseFloat(document.getElementById('itemQuantity').value);
    if(isNaN(qty)||qty<1){showMessage('Enter valid quantity (≥1)','error');return;}
    const idx=selectedItems.findIndex(i=>parseInt(i.id)===currentSelectedItem.id);
    if(idx>-1){selectedItems[idx].quantity=qty;showMessage('Quantity updated!','success');}
    else{selectedItems.push({...currentSelectedItem,quantity:qty});showMessage('Item added!','success');}
    updateItemsTable();closeItemDetails();closeItemModal();
}

/* ----------  TABLE / SUMMARY  ---------- */
function updateItemsTable(){
    const tbody=document.getElementById('selectedItemsBody');
    const noRow=document.getElementById('noItemsRow');
    if(selectedItems.length===0){
        noRow.style.display='';
        Array.from(tbody.children).forEach(r=>{if(r.id!=='noItemsRow')r.remove();});
        updateSummary();return;
    }
    noRow.style.display='none';
    Array.from(tbody.children).forEach(r=>{if(r.id!=='noItemsRow')r.remove();});
    selectedItems.forEach((it,idx)=>{
        const tr=document.createElement('tr');tr.className='hover:bg-gray-50';
        tr.innerHTML=`
            <td class="px-4 py-2">
                <p class="text-sm font-semibold text-gray-800">${escapeHtml(it.name)}</p>
                <p class="text-xs text-gray-500">${escapeHtml(it.code)}</p>
                <p class="text-xs text-gray-400">${escapeHtml(it.category)}</p>
                ${it.is_custom?'<span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded">Custom</span>':''}        <!-- ← NEW -->
            </td>
            <td class="px-4 py-2"><p class="text-sm text-gray-800">${it.quantity}</p></td>
            <td class="px-4 py-2"><p class="text-sm text-gray-800">${escapeHtml(it.unit)}</p></td>
            <td class="px-4 py-2">
                <button type="button" onclick="removeItem(${idx})" class="px-3 py-1 bg-red-600 text-white text-xs font-medium hover:bg-red-700 rounded">Remove</button>
            </td>`;
        tbody.appendChild(tr);
    });
    updateSummary();
}
function removeItem(idx){selectedItems.splice(idx,1);updateItemsTable();showMessage('Item removed','success');}
function updateSummary(){document.getElementById('totalItemsCount').textContent=selectedItems.length;}

/* ----------  LOAD INVENTORY  ---------- */
function loadInventoryItems(){
    const grid=document.getElementById('inventoryItemsGrid');
    fetch('{{ route('items.requisition') }}')
        .then(r=>r.ok?r.json():Promise.reject(r))
        .then(items=>{
            inventoryItems=items||[];
            if(!items.length){grid.innerHTML='<div class="col-span-2 text-center py-8"><i class="fas fa-box-open text-3xl mb-3 opacity-50"></i><p class="text-gray-500">No inventory items available.</p></div>';return;}
            grid.innerHTML='';
            items.forEach(it=>{
                const div=document.createElement('div');
                div.className='border border-gray-200 rounded-lg p-4 hover:bg-gray-50 inventory-item cursor-pointer';
                div.setAttribute('data-item-id',it.item_id);
                div.setAttribute('data-item-name',it.item_name);
                div.setAttribute('data-item-code',it.item_code);
                div.setAttribute('data-stock',it.item_stock);
                div.setAttribute('data-unit',it.item_unit);
                div.setAttribute('data-category',it.cat_name||'');
                div.setAttribute('data-is-custom',it.is_custom?1:0);        // ← NEW
                div.innerHTML=`
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="font-semibold text-gray-800">${escapeHtml(it.item_name)}</h4>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">${escapeHtml(it.item_code)}</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">${escapeHtml(it.cat_name||'')}</p>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-700">Stock: ${it.item_stock} ${escapeHtml(it.item_unit)}</span>
                        <div class="flex gap-2">
                            <button type="button" onclick="event.stopPropagation(); quickAddItem(this);" class="px-2 py-1 bg-green-600 text-white text-xs font-medium hover:bg-green-700 rounded" title="Quick Add (Qty: 1)"><i class="fas fa-plus"></i></button>
                            <button type="button" onclick="event.stopPropagation(); handleSelectItem(this);" class="px-3 py-1 bg-gray-800 text-white text-xs font-medium hover:bg-gray-700 rounded">Select</button>
                        </div>
                    </div>`;
                grid.appendChild(div);
            });
        })
        .catch(err=>{
            console.error(err);
            grid.innerHTML='<div class="col-span-2 text-center py-8"><i class="fas fa-exclamation-triangle text-2xl mb-3 opacity-50"></i><p class="text-gray-500">Error loading inventory items.</p></div>';
        });
}

/* ----------  SUBMIT REQUISITION  ---------- */
function submitRequisition(){
    if(selectedItems.length===0){showMessage('Add at least one item','error');return;}
    const form=document.getElementById('requisitionForm');
    const formData=new FormData(form);
    const priority=formData.get('req_priority');
    const purpose=formData.get('req_purpose')?.trim();
    if(!priority){showMessage('Select priority','error');return;}
    if(!purpose||purpose.length<10){showMessage('Enter detailed purpose (≥10 chars)','error');return;}

    const payload={
        req_priority:priority,
        req_purpose:purpose,
        items:selectedItems.map(i=>({item_id:parseInt(i.id),quantity:parseInt(i.quantity),is_custom:i.is_custom||false}))        // ← NEW
    };

    if(!confirm('Submit requisition?'))return;
    const btn=document.getElementById('submitRequisitionBtn');
    const orig=btn.innerHTML;
    btn.disabled=true;btn.innerHTML='<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...';

    fetch('{{ route('requisitions.store') }}',{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
        body:JSON.stringify(payload)
    })
        .then(async r=>{
            const d=await r.json();
            if(!r.ok){if(d.errors)throw new Error(Object.values(d.errors).flat().join(', '));throw new Error(d.message||'Server error');}
            return d;
        })
        .then(d=>{
            if(d.req_ref){ setReqRefDisplay(d.req_ref); }
            showMessage('Requisition submitted! Supervisors notified. Ref: '+(d.req_ref||''),'success');
            setTimeout(()=>{
                selectedItems=[];updateItemsTable();form.reset();
                document.getElementById('selectedPriority').textContent='Not set';
                setReqRefDisplay('Will be generated on submit'); updateSummary();
            },3000);
        })

        .catch(e=>{
            console.error(e);
            showMessage(e.message||'Submission failed','error');
        })
        .finally(()=>{btn.disabled=false;btn.innerHTML=orig;});
}

/* ----------  UTILS  ---------- */
function escapeHtml(text){
    if(!text)return'';
    const d=document.createElement('div');d.textContent=text;return d.innerHTML;
}
function showMessage(msg,type){
    const el=type==='success'?document.getElementById('successMessage'):document.getElementById('errorMessage');
    if(!el)return;
    el.textContent=msg;el.classList.remove('hidden');
    setTimeout(()=>el.classList.add('hidden'),5000);
}
</script>
@endsection