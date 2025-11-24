# Purchase Order Database Schema Alignment

## Overview
The database schema and model implementation are fully aligned with the purchase order functionality shown in `resources/views/Purchasing/purchase_orders/drafts.blade.php`.

## Database Schema ✅

### Purchase Orders Table
```sql
CREATE TABLE purchase_orders (
    id SERIAL PRIMARY KEY,
    po_number VARCHAR(50) NOT NULL UNIQUE,
    supplier_id INTEGER NOT NULL REFERENCES suppliers(id),
    order_date DATE NOT NULL DEFAULT CURRENT_DATE,
    expected_delivery_date DATE,
    actual_delivery_date DATE,
    status VARCHAR(20) NOT NULL DEFAULT 'draft' 
        CHECK (status IN ('draft', 'sent', 'confirmed', 'partial', 'completed', 'cancelled')),
    total_amount DECIMAL(12,2) DEFAULT 0.00,
    tax_amount DECIMAL(12,2) DEFAULT 0.00,
    discount_amount DECIMAL(12,2) DEFAULT 0.00,
    grand_total DECIMAL(12,2) DEFAULT 0.00,
    payment_terms INTEGER DEFAULT 30,
    notes TEXT,
    created_by INTEGER NOT NULL REFERENCES users(id),
    approved_by INTEGER REFERENCES users(id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

### Purchase Order Items Table
```sql
CREATE TABLE purchase_order_items (
    id SERIAL PRIMARY KEY,
    purchase_order_id INTEGER NOT NULL REFERENCES purchase_orders(id) ON DELETE CASCADE,
    item_id INTEGER NOT NULL REFERENCES items(id),
    quantity_ordered DECIMAL(10,3) NOT NULL,
    quantity_received DECIMAL(10,3) DEFAULT 0.000,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(12,2) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

### Purchase Request - Purchase Order Link Table (Many-to-Many)
```sql
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

## Model Implementation ✅

### Relationships
- `supplier()` - belongs to Supplier
- `createdBy()` - belongs to User (created_by)
- `approvedBy()` - belongs to User (approved_by)  
- `purchaseOrderItems()` - has many PurchaseOrderItem
- `sourcePurchaseRequests()` - many-to-many through link table

### Computed Properties (Accessors)
- `formatted_total` - ₱ formatted display
- `delivery_status` - delivery status with styling info
- `action_capabilities` - permissions-based action availability
- `total_items_count` - count of items in PO
- `total_quantity_ordered` - sum of ordered quantities
- `total_quantity_received` - sum of received quantities
- `is_overdue` - boolean for overdue delivery check
- `priority` - priority based on delivery date urgency
- `status_badge` - HTML badge for status display

### Helper Methods
- `getDraftOrdersWithFilters()` - filtered listing for drafts
- `getAvailableSuppliers()` - supplier dropdown for filters
- `getBulkActionCapabilitiesAttribute()` - bulk operation permissions

## Blade Template Requirements ✅

### Display Fields Supported
- ✅ `po_number` - PO identifier
- ✅ `supplier.name` - supplier name display
- ✅ `supplier.supplier_code` - supplier code
- ✅ `supplier.rating` - star rating display
- ✅ `total_items_count` - items count
- ✅ `total_quantity_ordered` - total ordered quantity
- ✅ `total_quantity_received` - total received quantity
- ✅ `formatted_total` - formatted total with currency
- ✅ `tax_amount` - tax display
- ✅ `discount_amount` - discount display
- ✅ `sourcePurchaseRequests.first()` - linked PR display
- ✅ `order_date` - order date
- ✅ `expected_delivery_date` - expected delivery
- ✅ `created_at` - creation timestamp
- ✅ `createdBy.name` - creator name
- ✅ `is_overdue` - overdue indicator
- ✅ `delivery_status` - delivery status
- ✅ `action_capabilities` - available actions

### Actions Supported
- ✅ `can_edit` - edit functionality
- ✅ `can_submit` - submit for approval
- ✅ `can_delete` - delete functionality
- ✅ `can_view` - view details
- ✅ Bulk actions with permissions

### Status Support
- ✅ `draft` - Draft status
- ✅ `sent` - Sent status  
- ✅ `confirmed` - Confirmed status
- ✅ `partial` - Partial delivery
- ✅ `completed` - Completed status
- ✅ `cancelled` - Cancelled status

## Key Features Alignment ✅

1. **Filtering & Search**
   - Database supports all filter fields
   - Model provides filtered query method
   - Template implements filtering UI

2. **Sorting**
   - All sort fields supported in database
   - Model implements dynamic sorting

3. **Pagination**
   - Model implements paginated results
   - Template supports per-page selection

4. **Export Functionality**
   - All data fields available for export

5. **Bulk Operations**
   - Permission-based bulk actions
   - Database and model support bulk operations

6. **Audit Trail**
   - created_at/updated_at timestamps
   - created_by/approved_by user tracking
   - Audit logs table available

## Summary

The database schema is **fully compatible** with the purchase order functionality shown in the drafts.blade.php template. All required fields, relationships, and computed properties are properly implemented and aligned between:

- ✅ Database Schema
- ✅ Eloquent Model (PurchaseOrder.php)  
- ✅ Blade Template (drafts.blade.php)

The system supports:
- Complete CRUD operations for purchase orders
- Supplier management and pricing
- Item tracking with quantities and costs
- Purchase request consolidation
- Status management and workflow
- Filtering, sorting, and pagination
- Export functionality
- Bulk operations with permissions
- Audit trail and user tracking

No database changes are required - the existing schema fully supports all purchase order features.