# Implementation Prompt: Multiple Purchase Requests per Purchase Order

## Task Overview
Implement multiple purchase request consolidation functionality in the WellKenz Bakery ERP system to allow users to select and combine multiple approved purchase requests into a single purchase order.

---

## Database Changes Required

### 1. Create Junction Table

```sql
-- Add this to your database migration or execute directly
CREATE TABLE purchase_request_purchase_order_link (
    id SERIAL PRIMARY KEY,
    purchase_request_id INTEGER NOT NULL REFERENCES purchase_requests(id) ON DELETE CASCADE,
    purchase_order_id INTEGER NOT NULL REFERENCES purchase_orders(id) ON DELETE CASCADE,
    consolidated_by INTEGER NOT NULL REFERENCES users(id),
    consolidated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(purchase_request_id, purchase_order_id)
);
```

### 2. Add Database Index

```sql
CREATE INDEX idx_pr_po_link_pr ON purchase_request_purchase_order_link(purchase_request_id);
CREATE INDEX idx_pr_po_link_po ON purchase_request_purchase_order_link(purchase_order_id);
```

---

## Backend Implementation

### 1. Create Model Class

**File**: `app/Models/PurchaseRequestPurchaseOrderLink.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestPurchaseOrderLink extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'purchase_order_id',
        'consolidated_by'
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function consolidatedBy()
    {
        return $this->belongsTo(User::class, 'consolidated_by');
    }
}
```

### 2. Update PurchasingController

**File**: `app/Http/Controllers/PurchasingController.php`

**Replace the `create` method with:**

```php
public function create(Request $request)
{
    // Handle multiple PR selection
    $selectedPRIds = $request->input('selected_pr_ids', []);
    
    if (empty($selectedPRIds)) {
        // Show PR selection screen with multi-select capability
        $purchaseRequests = PurchaseRequest::where('status', 'approved')
            ->with(['requestedBy', 'purchaseRequestItems.item.unit'])
            ->get();
        
        return view('Purchasing.purchase_orders.create_po', compact('purchaseRequests'));
    } else {
        // Handle multiple PRs consolidation
        $selectedPRs = PurchaseRequest::whereIn('id', $selectedPRIds)
            ->with(['requestedBy', 'purchaseRequestItems.item.unit'])
            ->get();
            
        // Pre-populate items from all selected PRs
        $prePopulatedItems = collect();
        foreach ($selectedPRs as $pr) {
            foreach ($pr->purchaseRequestItems as $item) {
                $prePopulatedItems->push([
                    'id' => $item->item->id,
                    'name' => $item->item->name,
                    'item_code' => $item->item->item_code,
                    'unit_symbol' => $item->item->unit->symbol,
                    'quantity_requested' => $item->quantity_requested,
                    'unit_price_estimate' => $item->unit_price_estimate,
                    'source_pr_id' => $pr->id,
                    'source_pr_number' => $pr->pr_number
                ]);
            }
        }
        
        $suppliers = Supplier::where('is_active', true)->get();
        
        return view('Purchasing.purchase_orders.create_po', [
            'selectedPurchaseRequest' => null,
            'selectedPRs' => $selectedPRs,
            'prePopulatedItems' => $prePopulatedItems,
            'suppliers' => $suppliers,
            'nextPoNumber' => $this->generatePONumber()
        ]);
    }
}
```

**Update the `store` method:**

```php
public function store(Request $request)
{
    $request->validate([
        'supplier_id' => 'required|exists:suppliers,id',
        'order_date' => 'required|date',
        'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
        'items' => 'required|array|min:1',
        'items.*.item_id' => 'required|exists:items,id',
        'items.*.quantity' => 'required|numeric|min:0.001',
        'items.*.unit_price' => 'required|numeric|min:0.01'
    ]);

    // Create purchase order
    $purchaseOrder = PurchaseOrder::create([
        'po_number' => $this->generatePONumber(),
        'supplier_id' => $request->supplier_id,
        'order_date' => $request->order_date,
        'expected_delivery_date' => $request->expected_delivery_date,
        'payment_terms' => $request->payment_terms ?? 30,
        'notes' => $request->notes,
        'created_by' => auth()->id(),
        'status' => 'draft'
    ]);

    // Create PO items
    foreach ($request->items as $itemData) {
        PurchaseOrderItem::create([
            'purchase_order_id' => $purchaseOrder->id,
            'item_id' => $itemData['item_id'],
            'quantity_ordered' => $itemData['quantity'],
            'unit_price' => $itemData['unit_price'],
            'total_price' => $itemData['quantity'] * $itemData['unit_price']
        ]);
    }

    // Link original PRs to this PO
    $selectedPRIds = $request->input('selected_pr_ids', []);
    foreach ($selectedPRIds as $prId) {
        PurchaseRequestPurchaseOrderLink::create([
            'purchase_request_id' => $prId,
            'purchase_order_id' => $purchaseOrder->id,
            'consolidated_by' => auth()->id()
        ]);
        
        // Update PR status to 'converted'
        PurchaseRequest::where('id', $prId)->update(['status' => 'converted']);
    }

    return redirect()->route('purchasing.po.index')
        ->with('success', 'Purchase order created successfully from ' . count($selectedPRIds) . ' purchase request(s)!');
}
```

### 3. Update Routes

**File**: `routes/web.php`

Ensure the route exists:
```php
Route::get('/purchasing/po/create', [PurchasingController::class, 'create'])->name('purchasing.po.create');
Route::post('/purchasing/po/store', [PurchasingController::class, 'store'])->name('purchasing.po.store');
```

---

## Frontend Implementation

### 1. Update PR Selection Table

**File**: `resources/views/Purchasing/purchase_orders/create_po.blade.php`

**Replace the table header (around line 115) with:**

```blade
<thead class="bg-gray-50">
    <tr>
        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
            <input type="checkbox" id="selectAllPRs" class="rounded border-gray-300 text-chocolate focus:ring-chocolate">
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PR Number</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Estimated Cost</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Date</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
    </tr>
</thead>
```

**Replace the table body (around line 126) with:**

```blade
<tbody class="bg-white divide-y divide-gray-200" id="prTableBody">
    @forelse($purchaseRequests as $pr)
        <tr class="hover:bg-gray-50 pr-row" data-pr="{{ strtolower($pr->pr_number) }}" data-department="{{ strtolower($pr->department ?? '') }}" data-priority="{{ $pr->priority }}">
            <td class="px-6 py-4 whitespace-nowrap text-center">
                <input type="checkbox" class="pr-checkbox rounded border-gray-300 text-chocolate focus:ring-chocolate" value="{{ $pr->id }}">
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ $pr->pr_number }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ $pr->department ?? 'N/A' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ $pr->requestedBy->name ?? 'N/A' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ $pr->purchaseRequestItems->count() }} items</div>
                <div class="text-sm text-gray-500">{{ $pr->purchaseRequestItems->sum('quantity_requested') }} total qty</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right">
                <div class="text-sm font-medium text-gray-900">₱{{ number_format($pr->total_estimated_cost, 2) }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ $pr->request_date->format('M d, Y') }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                @php
                    $priorityColors = [
                        'low' => 'text-blue-600 bg-blue-100',
                        'normal' => 'text-green-600 bg-green-100',
                        'high' => 'text-yellow-600 bg-yellow-100',
                        'urgent' => 'text-red-600 bg-red-100'
                    ];
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priorityColors[$pr->priority] ?? 'text-gray-600 bg-gray-100' }}">
                    {{ ucfirst($pr->priority) }}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
                <button type="button" 
                        onclick="showPRDetailsModal({{ $pr->id }})"
                        class="inline-flex items-center px-3 py-1 bg-blue-500 text-white text-xs font-medium rounded hover:bg-blue-600 transition mr-2">
                    <i class="fas fa-eye mr-1"></i> View
                </button>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="9" class="px-6 py-12 text-center">
                <div class="text-gray-500">
                    <i class="fas fa-clipboard-check text-4xl mb-4 block"></i>
                    <p class="text-lg font-medium">No approved purchase requests found</p>
                    <p class="text-sm mt-1">Approved purchase requests will appear here for conversion to purchase orders</p>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
```

### 2. Add Selection Footer

**Add after the table (around line 183):**

```blade
<div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-600">
            <span id="selectedCount">0</span> purchase request(s) selected
            <span id="selectedTotal" class="ml-4 font-medium">Total: ₱0.00</span>
        </div>
        <div class="flex gap-2">
            <button type="button" 
                    onclick="selectAllPRs()" 
                    class="inline-flex items-center px-3 py-1 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition">
                <i class="fas fa-check-double mr-1"></i> Select All
            </button>
            <button type="button" 
                    onclick="clearSelection()" 
                    class="inline-flex items-center px-3 py-1 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition">
                <i class="fas fa-times mr-1"></i> Clear
            </button>
            <button type="button" 
                    onclick="createConsolidatedPO()" 
                    class="inline-flex items-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition"
                    id="consolidateBtn" 
                    disabled>
                <i class="fas fa-plus mr-2"></i> Consolidate Selected PRs
            </button>
        </div>
    </div>
</div>
```

### 3. Update JavaScript Section

**Replace the existing JavaScript functions and add these new ones:**

```javascript
// Global managers
let modalManager, notificationManager, itemSearchManager, autoSaveManager, formManager;

// Global data
let purchaseRequestsData = @json($purchaseRequests);
let selectedPurchaseRequest = @json($selectedPurchaseRequest ?? null);
let prePopulatedItems = @json($prePopulatedItems ?? []);
let suppliersData = @json($suppliers);
let selectedPRs = @json($selectedPRs ?? []);

// Initialize managers
document.addEventListener('DOMContentLoaded', function() {
    modalManager = new ModalManager();
    notificationManager = new NotificationManager();
    itemSearchManager = new ItemSearchManager();
    autoSaveManager = new AutoSaveManager();
    formManager = new FormManager();
    
    // Set item search handler
    itemSearchManager.onItemSelected = (item) => {
        // This will be set by form manager
    };
    
    formManager.init();
    
    // Initialize PR multi-select if on PR selection screen
    if (!selectedPurchaseRequest && purchaseRequestsData.length > 0) {
        initializePRMultiSelect();
    }
});

// PR Multi-Select Functions
function initializePRMultiSelect() {
    const selectAllCheckbox = document.getElementById('selectAllPRs');
    const prCheckboxes = document.querySelectorAll('.pr-checkbox');
    const selectedCount = document.getElementById('selectedCount');
    const selectedTotal = document.getElementById('selectedTotal');
    const consolidateBtn = document.getElementById('consolidateBtn');

    if (!selectAllCheckbox) return;

    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        prCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectionUI();
    });

    // Individual checkbox changes
    prCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectionUI);
    });

    function updateSelectionUI() {
        const checkedBoxes = document.querySelectorAll('.pr-checkbox:checked');
        const count = checkedBoxes.length;
        selectedCount.textContent = count;
        consolidateBtn.disabled = count === 0;
        
        // Calculate total estimated cost
        let totalCost = 0;
        checkedBoxes.forEach(checkbox => {
            const prId = parseInt(checkbox.value);
            const pr = purchaseRequestsData.find(p => p.id === prId);
            if (pr) {
                totalCost += parseFloat(pr.total_estimated_cost) || 0;
            }
        });
        
        selectedTotal.textContent = `Total: ₱${totalCost.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
    }
}

function selectAllPRs() {
    const selectAllCheckbox = document.getElementById('selectAllPRs');
    const prCheckboxes = document.querySelectorAll('.pr-checkbox');
    
    prCheckboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    
    selectAllCheckbox.checked = true;
    updateSelectionUI();
}

function clearSelection() {
    const selectAllCheckbox = document.getElementById('selectAllPRs');
    const prCheckboxes = document.querySelectorAll('.pr-checkbox');
    
    prCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    selectAllCheckbox.checked = false;
    updateSelectionUI();
}

function updateSelectionUI() {
    const checkedBoxes = document.querySelectorAll('.pr-checkbox:checked');
    const count = checkedBoxes.length;
    const selectedCount = document.getElementById('selectedCount');
    const selectedTotal = document.getElementById('selectedTotal');
    const consolidateBtn = document.getElementById('consolidateBtn');

    if (selectedCount) selectedCount.textContent = count;
    if (consolidateBtn) consolidateBtn.disabled = count === 0;
    
    // Calculate total estimated cost
    let totalCost = 0;
    checkedBoxes.forEach(checkbox => {
        const prId = parseInt(checkbox.value);
        const pr = purchaseRequestsData.find(p => p.id === prId);
        if (pr) {
            totalCost += parseFloat(pr.total_estimated_cost) || 0;
        }
    });
    
    if (selectedTotal) {
        selectedTotal.textContent = `Total: ₱${totalCost.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
    }
}

function createConsolidatedPO() {
    const selectedPRIds = Array.from(document.querySelectorAll('.pr-checkbox:checked'))
        .map(checkbox => checkbox.value);

    if (selectedPRIds.length === 0) {
        notificationManager.show('error', 'Please select at least one purchase request');
        return;
    }

    // Confirm selection
    const totalCost = Array.from(document.querySelectorAll('.pr-checkbox:checked'))
        .reduce((sum, checkbox) => {
            const prId = parseInt(checkbox.value);
            const pr = purchaseRequestsData.find(p => p.id === prId);
            return sum + (parseFloat(pr?.total_estimated_cost) || 0);
        }, 0);

    const message = `Consolidate ${selectedPRIds.length} purchase request(s) into a single purchase order?\n\nTotal Estimated Cost: ₱${totalCost.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;

    if (!confirm(message)) {
        return;
    }

    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = "{{ route('purchasing.po.create') }}";
    
    // Add CSRF token
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfMeta.getAttribute('content');
        form.appendChild(csrfInput);
    }

    // Add selected PR IDs
    selectedPRIds.forEach(prId => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected_pr_ids[]';
        input.value = prId;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
}

function showPRDetailsModal(prId) {
    // Use existing modal functionality to show PR details
    if (typeof modalManager !== 'undefined') {
        modalManager.showPRDetails(prId);
    }
}
```

---

## Testing Checklist

### 1. Database Testing
- [ ] Junction table created successfully
- [ ] Indexes created for performance
- [ ] Foreign key constraints working

### 2. Backend Testing
- [ ] Model created and accessible
- [ ] Controller handles single PR (backward compatibility)
- [ ] Controller handles multiple PRs
- [ ] Validation works for consolidated orders
- [ ] PR status updated to 'converted'

### 3. Frontend Testing
- [ ] Checkboxes appear in PR table
- [ ] Select All / Clear functions work
- [ ] Selection counter updates correctly
- [ ] Total cost calculation accurate
- [ ] Consolidate button enables/disables properly
- [ ] Confirmation dialog appears

### 4. Integration Testing
- [ ] Multiple PRs can be selected
- [ ] Items from all PRs appear in PO form
- [ ] Original PRs linked to new PO
- [ ] Audit trail maintained
- [ ] No data loss during consolidation

---

## Deployment Instructions

1. **Backup Database**: Always backup before making schema changes
2. **Run Migration**: Execute the CREATE TABLE statement
3. **Deploy Code**: Upload all updated files
4. **Clear Cache**: Run `php artisan cache:clear`
5. **Test Functionality**: Verify in development environment
6. **Deploy to Production**: After testing is complete

---

## Post-Implementation Notes

- This implementation maintains backward compatibility with existing 1:1 PR-PO relationships
- The system allows mixing items from different departments/suppliers in consolidated POs
- Original PR numbers are preserved in the audit trail via the junction table
- Users can still create individual POs from single PRs as before

This implementation provides a solid foundation for multiple PR consolidation while keeping the system simple and maintainable.