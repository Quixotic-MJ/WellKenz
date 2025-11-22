# Purchase Request Backend Implementation

## Overview
This document describes the fully dynamic backend implementation for the Inventory Staff Purchase Request Interface, aligned with the WellKenz Bakery ERP database schema.

## System Purpose
The Purchase Request interface serves as a critical component in the inventory replenishment workflow where inventory staff proactively request purchases when item stock levels become critically low.

## Database Schema Integration

### Core Tables Used
1. **`items`** - Product/ingredient master list
2. **`current_stock`** - Real-time stock balances
3. **`categories`** - Product categorization
4. **`units`** - Measurement units
5. **`purchase_requests`** - Purchase request headers
6. **`purchase_request_items`** - Purchase request line items
7. **`users`** - User authentication and tracking
8. **`user_profiles`** - Extended user information (department)

## Backend Implementation

### Controller: `InventoryController.php`

#### 1. Main Interface Method: `index()` & `create()`
**Route:** `GET /inventory/outbound/purchase-requests`

**Purpose:** Display the purchase request interface with catalog and history

**Key Features:**
- Loads all active items with relationships (category, unit, current_stock)
- Calculates real-time stock status from `current_stock` table
- Retrieves user's purchase request history
- Provides statistics (pending, approved, rejected, draft)
- Supports filtering by status, department, and search

**Data Returned:**
```php
- items: Collection of active items with stock information
- categories: Active categories for filtering
- defaultDepartment: User's department from user_profiles
- purchaseRequests: Paginated PR history (user's own requests)
- stats: Request statistics
- departments: Unique departments for filtering
```

**Stock Status Calculation:**
```php
private function getStockStatus($item)
{
    $currentStock = $item->currentStockRecord->current_quantity;
    $reorderPoint = $item->reorder_point;
    $maxLevel = $item->max_stock_level;
    
    if ($currentStock <= 0) return 'out_of_stock';
    if ($currentStock <= $reorderPoint) return 'low_stock';
    if ($maxLevel > 0 && $currentStock >= $maxLevel * 0.8) return 'high_stock';
    return 'normal_stock';
}
```

#### 2. Create Purchase Request: `createPurchaseRequest()`
**Route:** `POST /inventory/outbound/purchase-requests`

**Purpose:** Store new purchase request with items

**Validation:**
- department: required, string, max 255
- priority: required, in (low, normal, high, urgent)
- request_date: required, date
- notes: nullable, string
- items: required, array, min 1
- items.*.item_id: required, exists in items table
- items.*.quantity_requested: required, numeric, min 0.01
- items.*.unit_price_estimate: required, numeric, min 0

**Process:**
1. Generate PR number: `PR-{YEAR}-{SEQUENCE}`
2. Calculate total estimated cost
3. Create purchase_requests record with status 'pending'
4. Create purchase_request_items records
5. Database triggers automatically update total_estimated_cost

**Response:**
```json
{
    "success": true,
    "message": "Purchase Request created successfully",
    "pr_number": "PR-2024-0001"
}
```

#### 3. Get Items API: `getItems()`
**Route:** `GET /inventory/purchase-requests/items`

**Purpose:** Fetch items dynamically with filtering

**Query Parameters:**
- search: Filter by name, item_code, or description
- category_id: Filter by category
- stock_status: Filter by stock level

**Features:**
- Eager loads relationships (category, unit, currentStockRecord)
- Calculates stock status dynamically
- Returns stock percentage for visual indicators
- Supports real-time filtering

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "item_code": "FLR001",
            "name": "All-Purpose Flour",
            "description": "Premium all-purpose wheat flour",
            "category": {"id": 1, "name": "Flour & Grains"},
            "unit": {"id": 1, "name": "Kilogram", "symbol": "kg"},
            "cost_price": 45.00,
            "current_stock": 150.50,
            "min_stock_level": 50.00,
            "max_stock_level": 500.00,
            "reorder_point": 100.00,
            "stock_status": "normal_stock",
            "stock_percentage": 30.1
        }
    ],
    "total": 50
}
```

#### 4. View Purchase Request Details: `show($id)`
**Route:** `GET /inventory/purchase-requests/{id}`

**Purpose:** Retrieve detailed information about a specific PR

**Features:**
- Loads PR with relationships (requestedBy, items, units)
- Formats monetary values
- Returns item details with quantities and costs

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "pr_number": "PR-2024-0001",
        "requested_by": "John Doe",
        "department": "Production",
        "priority": "high",
        "status": "pending",
        "notes": "Urgent restock needed",
        "request_date": "2024-01-20",
        "items": [...],
        "total_items": 5,
        "total_estimated_cost": 5000.00,
        "formatted_total": "₱5,000.00"
    }
}
```

#### 5. Cancel Purchase Request: `destroy($id)`
**Route:** `DELETE /inventory/purchase-requests/{id}`

**Purpose:** Cancel pending or draft purchase requests

**Validation:**
- Only allows cancellation of 'pending' or 'draft' status
- Prevents cancellation of approved/rejected requests

**Process:**
1. Verify request exists
2. Check status is cancellable
3. Delete related purchase_request_items (CASCADE)
4. Delete purchase_request record

#### 6. Get Categories: `getCategories()`
**Route:** `GET /inventory/purchase-requests/categories`

**Purpose:** Fetch active categories for filtering

#### 7. Get Departments: `getDepartments()`
**Route:** `GET /inventory/purchase-requests/departments`

**Purpose:** Fetch unique departments for autocomplete

## Workflow Integration

### 1. Low Stock Detection & Response
- Inventory staff monitor stock levels via the interface
- Stock status badges (Out of Stock, Low Stock, High Stock) help identify items needing attention
- Real-time data from `current_stock` table ensures accuracy

### 2. Strategic Purchase Requests
- Staff select items from the catalog
- Each item shows current stock levels and recommended reorder quantities
- Priority levels (Normal, High, Urgent, Low) help prioritize critical shortages

### 3. Approval Process Initiation
- Staff submit purchase requests through the interface
- Requests are routed to supervisors (status: 'pending')
- Supervisors review via `Supervisor/approvals/purchase_request.blade.php`
- System tracks request status (Pending, Approved, Rejected)

### 4. Procurement Pipeline Bridge
- **Current Role:** Inventory staff initiate requests when stock is low
- **Next Stage:** Supervisors review and approve requests
- **Final Stage:** Purchasing officers convert approved requests into Purchase Orders

## Key Features

### Real-time Stock Monitoring
- Displays current stock levels from `current_stock` table
- Calculates stock status dynamically based on reorder points
- Shows stock percentage for visual indicators

### Categorized Item Selection
- Filters items by category, stock level, and price range
- Search functionality across item name, code, and description
- Dynamic filtering without page reload

### Cost Estimation
- Provides estimated total costs for budget planning
- Uses `cost_price` from items table
- Automatically calculates line totals and grand total

### Department Tracking
- Links requests to specific departments for accountability
- Auto-fills user's department from `user_profiles` table
- Tracks department-wise request history

### User-Specific History
- Shows only the logged-in user's purchase requests
- Filters by status, department, and search terms
- Paginated results for better performance

## Database Relationships

### Item Model Relationships
```php
// Category relationship
public function category()
{
    return $this->belongsTo(Category::class);
}

// Unit relationship
public function unit()
{
    return $this->belongsTo(Unit::class);
}

// Current stock relationship
public function currentStockRecord()
{
    return $this->hasOne(CurrentStock::class);
}

// Accessor for current stock
public function getCurrentStockAttribute()
{
    return $this->currentStockRecord ? 
        $this->currentStockRecord->current_quantity : 0;
}
```

### Purchase Request Relationships
```php
// Requested by user
public function requestedBy()
{
    return $this->belongsTo(User::class, 'requested_by');
}

// Purchase request items
public function purchaseRequestItems()
{
    return $this->hasMany(PurchaseRequestItem::class);
}
```

## Routes Configuration

```php
// Main interface (catalog + history)
Route::get('/outbound/purchase-requests', [InventoryController::class, 'index'])
    ->name('purchase-requests.index');

// Create interface (same as index)
Route::get('/outbound/purchase-requests/create', [InventoryController::class, 'create'])
    ->name('purchase-requests.create');

// Store new purchase request
Route::post('/outbound/purchase-requests', [InventoryController::class, 'createPurchaseRequest'])
    ->name('purchase-requests.store');

// View details
Route::get('/purchase-requests/{id}', [InventoryController::class, 'show'])
    ->name('purchase-requests.show');

// Cancel request
Route::delete('/purchase-requests/{id}', [InventoryController::class, 'destroy'])
    ->name('purchase-requests.destroy');

// API endpoints
Route::get('/purchase-requests/items', [InventoryController::class, 'getItems'])
    ->name('purchase-requests.items');
Route::get('/purchase-requests/categories', [InventoryController::class, 'getCategories'])
    ->name('purchase-requests.categories');
Route::get('/purchase-requests/departments', [InventoryController::class, 'getDepartments'])
    ->name('purchase-requests.departments');
```

## Security & Authorization

### Middleware Protection
- All routes protected by `auth` middleware
- Role-based access: `role:inventory`
- Only inventory staff can access these routes

### Data Isolation
- Users see only their own purchase requests
- Filtered by `requested_by` = current user ID
- Prevents unauthorized access to other users' data

### Validation
- Server-side validation for all inputs
- CSRF token protection on POST/DELETE requests
- SQL injection prevention via Eloquent ORM

## Performance Optimizations

### Eager Loading
- Loads relationships in single query to prevent N+1 problems
- Uses `with()` for category, unit, currentStockRecord

### Pagination
- Purchase request history paginated (10 per page)
- Reduces memory usage and improves response time

### Selective Data Loading
- Only loads active items (`is_active = true`)
- Filters data at database level, not in PHP

### Caching Opportunities
- Categories can be cached (rarely change)
- Stock status calculated on-demand (always fresh)

## Error Handling

### Try-Catch Blocks
- All controller methods wrapped in try-catch
- Logs errors to Laravel log file
- Returns user-friendly error messages

### Validation Errors
- Returns 422 status with validation messages
- Frontend displays errors to user
- Prevents invalid data from reaching database

### Database Errors
- Transaction rollback on failure
- Maintains data integrity
- Returns 500 status with error message

## Frontend Integration

### Data Binding
- Items passed as JSON to JavaScript
- Categories populated in filter dropdown
- Stats displayed in header badges

### AJAX Requests
- Submit PR without page reload
- Fetch items dynamically
- View details in modal

### Real-time Updates
- Stock status badges update based on data
- Cart total calculates automatically
- Form validation before submission

## Testing Recommendations

### Unit Tests
- Test stock status calculation logic
- Validate PR number generation
- Test cost calculation accuracy

### Integration Tests
- Test complete PR creation workflow
- Verify database relationships
- Test filtering and search functionality

### User Acceptance Tests
- Inventory staff can create PR successfully
- Stock status displays correctly
- Filtering works as expected
- Approval workflow functions properly

## Future Enhancements

1. **Notifications**
   - Alert inventory staff when items reach reorder point
   - Notify when PR is approved/rejected

2. **Bulk Operations**
   - Create PR from low stock items automatically
   - Bulk approve/reject for supervisors

3. **Analytics**
   - Track most requested items
   - Analyze request patterns by department
   - Forecast future needs

4. **Integration**
   - Auto-convert approved PRs to POs
   - Link to supplier pricing
   - Track delivery status

## Conclusion

The backend implementation is fully dynamic, database-driven, and follows Laravel best practices. It integrates seamlessly with the WellKenz Bakery ERP database schema and provides a robust foundation for the inventory replenishment workflow.

All data is fetched from the database in real-time, ensuring accuracy and consistency across the system. The implementation supports the complete workflow from low stock detection to purchase request approval and procurement.
