# Minimal Implementation: Multiple PRs per Purchase Order

## Simple Solution Overview

To make the `create_po.blade.php` page work with multiple purchase requests per purchase order, you only need **one additional database table** and **simple UI/backend changes**.

## Required Database Change

### 1. Add Junction Table

```sql
-- Simple table to link multiple PRs to one PO
CREATE TABLE purchase_request_purchase_order_link (
    id SERIAL PRIMARY KEY,
    purchase_request_id INTEGER NOT NULL REFERENCES purchase_requests(id) ON DELETE CASCADE,
    purchase_order_id INTEGER NOT NULL REFERENCES purchase_orders(id) ON DELETE CASCADE,
    consolidated_by INTEGER NOT NULL REFERENCES users(id),
    consolidated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(purchase_request_id, purchase_order_id)
);
```

**That's it!** This single table enables the many-to-many relationship.

## UI Changes Required

### 1. Add Checkboxes to PR Selection Table

In `resources/views/Purchasing/purchase_orders/create_po.blade.php`, add checkboxes:

```blade
{{-- Add checkbox column to the PR table header --}}
<th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
    <input type="checkbox" id="selectAllPRs" class="rounded">
</th>

{{-- Add checkbox to each PR row --}}
<td class="px-6 py-4 text-center">
    <input type="checkbox" class="pr-checkbox rounded" value="{{ $pr->id }}">
</td>
```

### 2. Update Create PO Button

Replace single "Create PO" button with:

```blade
<div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-600">
            <span id="selectedCount">0</span> purchase request(s) selected
        </div>
        <button type="button" 
                onclick="createConsolidatedPO()" 
                class="inline-flex items-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition"
                id="consolidateBtn" 
                disabled>
            <i class="fas fa-plus mr-2"></i> Consolidate Selected PRs
        </button>
    </div>
</div>
```

### 3. Add JavaScript for Multi-Select

```javascript
// Add to the existing JavaScript section
document.addEventListener('DOMContentLoaded', function() {
    setupPRMultiSelect();
});

function setupPRMultiSelect() {
    const selectAllCheckbox = document.getElementById('selectAllPRs');
    const prCheckboxes = document.querySelectorAll('.pr-checkbox');
    const selectedCount = document.getElementById('selectedCount');
    const consolidateBtn = document.getElementById('consolidateBtn');

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
    }
}

function createConsolidatedPO() {
    const selectedPRIds = Array.from(document.querySelectorAll('.pr-checkbox:checked'))
        .map(checkbox => checkbox.value);

    if (selectedPRIds.length === 0) {
        alert('Please select at least one purchase request');
        return;
    }

    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = "{{ route('purchasing.po.create') }}";
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

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
```

## Backend Changes Required

### 1. Update Controller to Handle Multiple PRs

In your PurchasingController, modify the `create` method:

```php
public function create(Request $request)
{
    // Handle multiple PR selection
    $selectedPRIds = $request->input('selected_pr_ids', []);
    
    if (empty($selectedPRIds)) {
        // Show PR selection screen
        $purchaseRequests = PurchaseRequest::where('status', 'approved')
            ->with(['requestedBy', 'purchaseRequestItems.item'])
            ->get();
        
        return view('Purchasing.purchase_orders.create_po', compact('purchaseRequests'));
    } else {
        // Handle multiple PRs consolidation
        $selectedPRs = PurchaseRequest::whereIn('id', $selectedPRIds)
            ->with(['requestedBy', 'purchaseRequestItems.item.unit'])
            ->get();
            
        // Check if all PRs are from the same supplier or allow mixed suppliers
        // For simplicity, we'll allow mixed suppliers
        
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
        
        // Group items by supplier for better UX
        $groupedItems = $prePopulatedItems->groupBy(function($item) {
            // You might want to get supplier info from supplier_items table
            return 'default'; // Simplified for now
        });
        
        $suppliers = Supplier::where('is_active', true)->get();
        
        return view('Purchasing.purchase_orders.create_po', [
            'selectedPurchaseRequest' => null, // We're using multiple PRs
            'selectedPRs' => $selectedPRs,
            'prePopulatedItems' => $prePopulatedItems,
            'suppliers' => $suppliers,
            'nextPoNumber' => $this->generatePONumber()
        ]);
    }
}
```

### 2. Update Store Method to Link PRs to PO

```php
public function store(Request $request)
{
    // Validate and create purchase order
    $purchaseOrder = PurchaseOrder::create([
        'po_number' => $this->generatePONumber(),
        'supplier_id' => $request->supplier_id,
        'order_date' => $request->order_date,
        'expected_delivery_date' => $request->expected_delivery_date,
        'payment_terms' => $request->payment_terms,
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
        ->with('success', 'Purchase order created successfully!');
}
```

### 3. Add Model for Junction Table

```php
// app/Models/PurchaseRequestPurchaseOrderLink.php
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

## Summary

**Minimal Changes Required:**
1. ✅ **1 Database Table**: `purchase_request_purchase_order_link`
2. ✅ **UI Updates**: Add checkboxes and consolidate button
3. ✅ **JavaScript**: Multi-select functionality
4. ✅ **Backend**: Handle multiple PRs in controller

**No complex features needed:**
- ❌ No budget management
- ❌ No approval workflows  
- ❌ No advanced business rules
- ❌ No contract management

This simple implementation allows users to select multiple approved PRs, consolidate them into a single PO, and maintain proper links for audit trails - all with minimal changes to your existing system.