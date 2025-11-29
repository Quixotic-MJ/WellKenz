# Employee, Inventory, Supervisor & Purchasing DDD Migration Plan

> Goal: Fully migrate from the legacy monolithic controllers (`EmployeeController`, `InventoryController`, `SupervisorController`, `PurchasingController`) to the new DDD/controller structure so that the monolithic controllers can be safely deleted.

---

## 1. Current State Overview

- **Monolithic controllers (legacy)**
  - `App\Http\Controllers\EmployeeController`
  - `App\Http\Controllers\InventoryController`
  - `App\Http\Controllers\SupervisorController`
  - `App\Http\Controllers\PurchasingController`

- **New DDD-style controllers already present** (non‑exhaustive):
  - `App\Http\Controllers\Employee\DashboardController`
  - `App\Http\Controllers\Employee\RequisitionController`
  - `App\Http\Controllers\Employee\ProductionController`
  - `App\Http\Controllers\Employee\RecipeController`
  - `App\Http\Controllers\Employee\Notifications\NotificationController`
  - `App\Http\Controllers\Inventory\GeneralController`
  - `App\Http\Controllers\Inventory\Inbound\ReceivingController`
  - `App\Http\Controllers\Inventory\Inbound\BatchController`
  - `App\Http\Controllers\Inventory\Inbound\RtvController`
  - `App\Http\Controllers\Inventory\Outbound\FulfillmentController`
  - `App\Http\Controllers\Inventory\Outbound\PurchaseRequestController`
  - `App\Http\Controllers\Inventory\StockManagement\BatchLookupController`
  - `App\Http\Controllers\Inventory\Notifications\NotificationController`
  - `App\Http\Controllers\Supervisor\DashboardController`
  - `App\Http\Controllers\Supervisor\RequisitionController`
  - `App\Http\Controllers\Supervisor\ApprovalsController`
  - `App\Http\Controllers\Supervisor\InventoryController`
  - `App\Http\Controllers\Supervisor\ReportsController`
  - `App\Http\Controllers\Supervisor\SettingsController`
  - `App\Http\Controllers\Supervisor\Notifications\NotificationController`
  - `App\Http\Controllers\Purchasing\DashboardController`
  - `App\Http\Controllers\Purchasing\PurchaseOrderController`
  - `App\Http\Controllers\Purchasing\SupplierController`
  - `App\Http\Controllers\Purchasing\PriceListController`
  - `App\Http\Controllers\Purchasing\ReportController`
  - `App\Http\Controllers\Purchasing\Notifications\NotificationController`

- **Routes already migrated to DDD controllers**
  - `routes/web.php` is already wired to the new `Employee\*`, `Inventory\*`, `Supervisor\*`, `Purchasing\*` controllers for the main flows (dashboards, requisitions, production, POs, suppliers, inventory inbound/outbound, notifications, etc.).
  - The monolithic controllers are currently **not** used for most routes but still contain domain logic that should either:
    - Be confirmed as **fully duplicated** in the new controllers, or
    - Be **ported** into appropriate DDD controllers / services, or
    - Be **explicitly deprecated** and removed.

---

## 2. Target DDD Structure (High-Level)

Follow the same philosophy used for `AdminController` split (see `ADMIN_CONTROLLER_REFACTORING_STATUS.md` and `refactoring_implementation_plan.md`):

- **Employee bounded context**
  - `Employee\DashboardController` – employee home widgets, summary cards.
  - `Employee\RequisitionController` – employee requisition creation, history, details, confirm receipt.
  - `Employee\ProductionController` – production log, manual/recipe-based production entries.
  - `Employee\RecipeController` – recipe CRUD, recipe-of-the-day, AJAX recipe details.
  - `Employee\Notifications\NotificationController` – employee-facing notifications.

- **Inventory bounded context**
  - `Inventory\GeneralController` – inventory dashboard metrics, widgets, overview.
  - `Inventory\Inbound\ReceivingController` – receiving deliveries and updating batches/stock.
  - `Inventory\Inbound\BatchController` – batch logs, labels, status changes.
  - `Inventory\Inbound\RtvController` – Return-to-vendor operations.
  - `Inventory\Outbound\FulfillmentController` – requisition picking, FEFO, issuance.
  - `Inventory\Outbound\PurchaseRequestController` – inventory-initiated purchase requests (catalog + history + APIs).
  - `Inventory\StockManagement\BatchLookupController` – stock lookup & batch details.
  - `Inventory\Notifications\NotificationController` – inventory notifications.

- **Supervisor bounded context**
  - `Supervisor\DashboardController` – dashboard (critical stock, recent requisitions, metrics).
  - `Supervisor\RequisitionController` – *new* requisition approval UX & flows.
  - `Supervisor\ApprovalsController` – legacy approvals and bulk actions.
  - `Supervisor\InventoryController` – stock overview, adjustments, exports.
  - `Supervisor\ReportsController` – expiry/use-first lists, batch reports.
  - `Supervisor\SettingsController` – stock level configuration.
  - `Supervisor\Notifications\NotificationController` – supervisor notifications.

- **Purchasing bounded context**
  - `Purchasing\DashboardController` – purchasing home dashboard.
  - `Purchasing\PurchaseOrderController` – PO lifecycle (draft → sent → confirmed → completed).
  - `Purchasing\SupplierController` – supplier master data.
  - `Purchasing\PriceListController` – supplier price lists.
  - `Purchasing\ReportController` – purchase history, performance, RTV reports.
  - `Purchasing\Notifications\NotificationController` – purchasing notifications.

**Target outcome**: all features in the legacy controllers are either:
- Implemented in one of the new controllers above, or
- Intentionally dropped as dead/obsolete code.

---

## 3. Global Migration Strategy

- **Approach**: incremental, context-by-context, following the validated pattern of the Admin refactor.
- **Constraints**:
  - Do not break existing front-end routes, Blade views, or AJAX calls.
  - Maintain notification flows between Employee ⇄ Supervisor ⇄ Inventory ⇄ Purchasing.
  - Preserve audit logging behavior where present.

### 3.1 Phases

- **Phase E** – EmployeeController deconstruction
- **Phase I** – InventoryController deconstruction
- **Phase S** – SupervisorController deconstruction
- **Phase P** – PurchasingController deconstruction
- **Phase X** – Final cleanup & deletion of monolithic controllers

Each phase follows the same pattern:

1. **Inventory legacy methods** in the monolithic controller (what is still unique there?).
2. **Find or create DDD destination controller** for each method.
3. **Move logic** into the new controller (or service), preserving validation and side-effects.
4. **Wire routes** (or confirm existing new routes already cover behavior).
5. **Smoke-test + regression** for that context.
6. **Mark legacy method as deprecated** (temporary) then remove once everything passes.

---

## 4. Phase E – EmployeeController Migration

### 4.1 Map legacy responsibilities

From `EmployeeController` (examples from current file):

- **Dashboard & data widgets**
  - `home()` – loads user profile, active requisitions, incoming deliveries, notifications, recipe of the day.
  - `getActiveRequisitions()`
  - `getIncomingDeliveries()`
  - `getNotifications()`
  - `getRecipeOfTheDay()`

- **Requisition lifecycle (employee-side)**
  - `showCreateRequisition()` – show catalog + categories + department.
  - `createRequisition()` – validate cart, create `Requisition` + `RequisitionItem`s, notify supervisors.
  - `requisitionHistory()` – filter/sort/paginate requisitions.
  - `confirmReceipt(Requisition $requisition)` – mark requisition completed + notifications.

- **Production / Recipes**
  - `productionLog()` – list finished goods / recipes.
  - `storeProduction()` – log new production batches, create `Batch` + `StockMovement` + notifications.
  - `recipes()` – list recipes with details.
  - `getRecipeDetails(Recipe $recipe)` – AJAX recipe JSON.
  - `createRecipe()`, `updateRecipe()`, `deleteRecipe()` – recipe CRUD + notifications.

- **Misc/utility**
  - Any additional helper methods such as `checkRejectQuantitySupport()` (currently still routed via `EmployeeController` in `web.php`).

### 4.2 Target mapping to DDD controllers

- **Employee dashboard**
  - Move `home()`, `getActiveRequisitions()`, `getIncomingDeliveries()`, `getNotifications()`, `getRecipeOfTheDay()`
    - **Destination**: `Employee\DashboardController`
    - Expose internal helpers as `protected`/`private` or as injected services if reused elsewhere.

- **Requisitions (employee)**
  - Move `showCreateRequisition()`, `createRequisition()`, `requisitionHistory()`, `confirmReceipt()`
    - **Destination**: `Employee\RequisitionController`
    - Ensure route names align with existing ones under `employee.requisitions.*`.
    - Maintain notification semantics to supervisors and employees.

- **Production & Recipes**
  - Move `productionLog()` and `storeProduction()`
    - **Destination**: `Employee\ProductionController`
  - Move `recipes()`, `getRecipeDetails()`, `createRecipe()`, `updateRecipe()`, `deleteRecipe()`
    - **Destination**: `Employee\RecipeController`

- **Utility**
  - Move/check any helper like `checkRejectQuantitySupport()`
    - **Destination**: `Employee\ProductionController` or a dedicated small controller (e.g. `Employee\SupportController`) if used only in one view.

### 4.3 Concrete steps

1. **Scan `EmployeeController` for all public methods** and tag each with target destination:
   - `Dashboard`, `Requisition`, `Production`, `Recipe`, or `Utility`.

2. **For each method group**:
   - If the corresponding method already exists in `Employee\*Controller`:
     - **Diff logic** (old vs new) and merge any missing pieces (validation, notifications, special filters).
   - If not yet implemented:
     - **Create method** in the appropriate DDD controller.
     - Copy over logic from `EmployeeController` and refactor for clarity (no behavior changes).

3. **Update routes (`routes/web.php`)**:
   - Confirm that all employee-facing routes point to the new controllers:
     - `employee.dashboard` → `Employee\DashboardController@home` (already done).
     - `employee.requisitions.*` → `Employee\RequisitionController` (already done).
     - `employee.production.*` → `Employee\ProductionController` (already done for log/store).
     - `employee.recipes.index` → `Employee\RecipeController@recipes` (already done).
   - Replace any remaining `EmployeeController` usages (e.g. `production.check-reject-support`) with their new `Employee\*` counterparts.

4. **Run manual regression on Employee flows**:
   - Create a requisition.
   - View requisition history & details.
   - Confirm receipt flow.
   - View production log and submit a new production entry.
   - View recipes list, open recipe modal (AJAX), and perform recipe CRUD.

5. **Mark `EmployeeController` as legacy**:
   - Temporarily keep the file with only stub methods throwing `404`/`abort(404)` (optional) while you deploy.
   - After full regression, **delete `EmployeeController.php`**.

---

## 5. Phase I – InventoryController Migration

### 5.1 Map legacy responsibilities

From `InventoryController` (partial list based on current file):

- **Dashboard & alerts**
  - `home()` – inventory dashboard, metrics, pending POs, expiring batches, requisition widgets.
  - `createAutoExpiryNotifications()` – internal helper.
  - `notifyExpiringBatch()` – internal helper.
  - `getExpiryPriority()`, `getExpiryMessage()` – internal helpers.

- **FEFO batch picking / quarantine**
  - `pickBatch($batchId)` – marks batch as quarantine + stock movement + notifications.
  - `notifyFefoReservation()` – internal helper to message production.

- **Inventory-originated purchase requests (if any legacy methods pre-split)**
  - Legacy `index()`, `create()`, `show()`, `destroy()` of purchase requests.
  - `getItems()`, `getCategories()`, `getCategoriesForRtvBulk()`, `getDepartments()` – API endpoints.

- **Requisition fulfillment from inventory side**
  - `startPicking($requisitionId)` – mark requisition fulfilled + notifications.

### 5.2 Target mapping to DDD controllers

- **Inventory dashboard & alerts**
  - Move `home()`, `createAutoExpiryNotifications()`, `notifyExpiringBatch()`, `getExpiryPriority()`, `getExpiryMessage()`
    - **Destination**: `Inventory\GeneralController`
    - Make helper methods `protected` or create a small service (`ExpiryNotificationService`) if used by Supervisor reports.

- **FEFO batch picking**
  - Move `pickBatch()` and `notifyFefoReservation()`
    - **Destination**: `Inventory\Outbound\FulfillmentController`
    - Align with routes:
      - `inventory.batches.pick` → Fulfillment controller.

- **Inventory purchase requests & item/category APIs**
  - Map legacy purchase-request-related methods:
    - **Destination**: `Inventory\Outbound\PurchaseRequestController`
    - Methods include: `index/create/show/destroy`, `getItems`, `getCategories`, `getCategoriesForRtvBulk`, `getDepartments`.

- **Requisition fulfillment**
  - Move `startPicking()`
    - **Destination**: `Inventory\Outbound\FulfillmentController`.

### 5.3 Concrete steps

1. **Enumerate all public methods** in `InventoryController` and classify them as:
   - Dashboard/metrics, Purchase Requests, FEFO, RTV, Requisition fulfillment, Misc.

2. **Per category**, ensure matching methods exist in:
   - `Inventory\GeneralController`
   - `Inventory\Outbound\FulfillmentController`
   - `Inventory\Outbound\PurchaseRequestController`
   - `Inventory\StockManagement\BatchLookupController`

3. **Refactor methods**:
   - Move implementation into the above controllers.
   - Extract generic logic (e.g., expiry messaging, FEFO notes) into private methods or dedicated service classes under `App\Services\Inventory\*` if reused.

4. **Routes check** (`routes/web.php`):
   - Confirm all `inventory.*` routes refer to DDD controllers (they already mostly do):
     - Dashboard → `GeneralController@home`.
     - Inbound → `ReceivingController`, `BatchController`, `RtvController`.
     - Outbound → `FulfillmentController`, `PurchaseRequestController`.
     - Lookup & notifications → BatchLookup + InventoryNotificationController.
   - Remove any remaining `InventoryController` references.

5. **Manual tests**:
   - Inventory dashboard overview.
   - Expiry warnings and auto-notifications.
   - FEFO / quarantine flow from dashboard and outbound.
   - Purchase requests (UI + JSON APIs).
   - Requisition fulfillment (start picking, confirm issuance).

6. **Delete `InventoryController.php`** once satisfied.

---

## 6. Phase S – SupervisorController Migration

### 6.1 Map legacy responsibilities

From `SupervisorController` (partial list from current file):

- **Dashboard & metrics**
  - `home()` – loads critical stock items, pending approvals, recent requisitions.
  - `getCriticalStockItems()` – stock & reorder analysis.
  - `getPendingApprovals()` – counts.
  - `getRecentRequisitions()` + `formatTimeAgo()` – recent requisition cards.
  - `getStockOverview()` – JSON stats for supervisor dashboard.

- **Purchase request approvals**
  - `approvePurchaseRequest()`, `rejectPurchaseRequest()` – includes notifications + audit logs.
  - `getPurchaseRequestDetails()` – JSON for modal.
  - `bulkApprovePurchaseRequests()` – bulk approval.

- **Requisition approvals**
  - `approveRequisition()` – stock checks + audit log + notifications.
  - Additional methods (later in file) for rejecting / modifying requisitions.

### 6.2 Target mapping to DDD controllers

- **Dashboard & stock metrics**
  - Move `home()`, `getCriticalStockItems()`, `getPendingApprovals()`, `getRecentRequisitions()`, `formatTimeAgo()`, `getStockOverview()`
    - **Destination**: `Supervisor\DashboardController`
    - Shared stock-calculation logic can be extracted into a `Supervisor\InventoryController` or a shared service if needed.

- **Requisition approvals (new vs legacy)**
  - You already have:
    - `Supervisor\RequisitionController` (new approval system, new routes)
    - `Supervisor\ApprovalsController` (legacy approvals + bulk ops, still used by some routes)

  - Strategy:
    - Gradually port logic from `SupervisorController` → `Supervisor\ApprovalsController` and `Supervisor\RequisitionController`, ensuring:
      - AuditLog writes are preserved.
      - Notification payloads (metadata) remain consistent.

- **Purchase request approvals**
  - Map `approvePurchaseRequest()`, `rejectPurchaseRequest()`, `getPurchaseRequestDetails()`, `bulkApprovePurchaseRequests()`
    - **Destination**: `Supervisor\ApprovalsController` (already referenced by routes under `/supervisor/purchase-requests/...`).

- **Requisition approvals & modifications**
  - Map `approveRequisition()`, `modifyRequisitionQuantity()`, other requisition modification endpoints
    - **Destination**: `Supervisor\ApprovalsController` and/or `Supervisor\RequisitionController` depending on UI.

### 6.3 Concrete steps

1. **List all public methods** in `SupervisorController` and classify as:
   - Dashboard/metrics
   - Requisition approvals
   - Purchase request approvals
   - Stock / adjustments utilities

2. **Dashboard move**:
   - Implement `home()` logic in `Supervisor\DashboardController`.
   - Move helper methods (`getCriticalStockItems`, `getPendingApprovals`, `getRecentRequisitions`, `formatTimeAgo`, `getStockOverview`).

3. **Approvals mapping**:
   - For each approval method:
     - Check if `Supervisor\ApprovalsController` already has an equivalent.
     - If yes, reconcile behavior (audit logging, notifications) and **delete** the old one.
     - If not, move from `SupervisorController` and adapt to new route signatures.

4. **Routes alignment** in `routes/web.php`:
   - New requisition routes already mapped to `Supervisor\RequisitionController`.
   - Legacy approval routes mapped to `Supervisor\ApprovalsController`.
   - Remove any `SupervisorController` route usage (if any remains).

5. **Manual tests**:
   - Supervisor dashboard (cards & metrics & charts).
   - Approve/reject requisitions (including validation of stock availability).
   - Approve/reject purchase requests (including audit logs and notifications).
   - Bulk approve flows.

6. **Delete `SupervisorController.php`** when all flows are proven stable.

---

## 7. Phase P – PurchasingController Migration

### 7.1 Map legacy responsibilities

From `PurchasingController` (partial list):

- **Dashboard**
  - `home()` – low stock, open PO value, open PO count, overdue deliveries, recent POs, frequent suppliers.
  - Helpers: `getLowStockItems()`, `getOpenPurchaseOrderValue()`, `getOpenPurchaseOrderCount()`, `getOverdueDeliveries()`, `getRecentPurchaseOrders()`, `getFrequentSuppliers()`, `isOrderOverdue()`.

- **Metrics / summary APIs**
  - `getDashboardSummary()` – counts + KPIs.
  - `getAverageDeliveryTime()` – helper.

- **PO list / views**
  - `purchaseOrders()`, `showPurchaseOrder()`, `drafts()`, `openOrders()`, `partialOrders()`, `completedHistory()`, `exportCompletedHistory()`.

- **Suppliers & price list**
  - `suppliers()`, `storeSupplier()`, `updateSupplier()`, `destroySupplier()`, `toggleSupplierStatus()`.
  - `supplierPriceList()`, `showPriceUpdate()`, `updateSupplierItemPrice()`.

- **Item/supplier search APIs**
  - `searchItems()`, `getSupplierItems()`.

### 7.2 Target mapping to DDD controllers

- **Dashboard & metrics**
  - Move `home()`, `getLowStockItems()`, `getOpenPurchaseOrderValue()`, `getOpenPurchaseOrderCount()`, `getOverdueDeliveries()`, `getRecentPurchaseOrders()`, `getFrequentSuppliers()`, `isOrderOverdue()`, `getDashboardSummary()`, `getAverageDeliveryTime()`
    - **Destination**: `Purchasing\DashboardController`
    - Optionally split reporting helpers into `Purchasing\ReportController` if used there.

- **PO list & lifecycle**
  - Move `purchaseOrders()`, `showPurchaseOrder()`, `drafts()`, `openOrders()`, `partialOrders()`, `completedHistory()`, `exportCompletedHistory()`
    - **Destination**:
      - `Purchasing\PurchaseOrderController` – interactive UI routes (index, show, create, edit, open, drafts, partial, etc.).
      - `Purchasing\ReportController` – historical/analytics: completed history, export CSV.

- **Suppliers & price list**
  - Map all supplier & pricing methods
    - **Destination**:
      - `Purchasing\SupplierController` – supplier CRUD + filters.
      - `Purchasing\PriceListController` – price list views, bulk edit, update single item.

- **APIs** (`searchItems`, `getSupplierItems`, etc.)
  - Move to:
    - `Purchasing\PurchaseOrderController` – `searchItems`, `getSupplierItemsForPRs`.
    - `Purchasing\PriceListController` – supplier items endpoints.

### 7.3 Concrete steps

1. **Enumerate all public methods** in `PurchasingController` and assign a destination:
   - Dashboard, POs, Suppliers, PriceList, Reports, APIs.

2. **Check existing `Purchasing\*` controllers**:
   - Many of these methods already appear to be implemented there (based on route wiring).
   - For each method in `PurchasingController`:
     - Diff vs the equivalent in `Purchasing\*` and merge any missing business logic.

3. **Routes verification** (`routes/web.php`):
   - Confirm `/purchasing/...` routes all point to namespaced controllers (they already do).
   - Remove any remaining references to `PurchasingController`.

4. **Manual regression**:
   - Purchasing dashboard metrics & widgets.
   - PO creation, editing, submission, acknowledgment, printing.
   - Supplier masterlist filters, supplier CRUD, toggling active/inactive.
   - Price list browsing, edits, CSV exports.

5. **Delete `PurchasingController.php`** after testing.

---

## 8. Cross-Cutting Concerns

### 8.1 Notifications

- Ensure that all notifications originally created in monolithic controllers still fire from the new DDD controllers:
  - Employee → Supervisor (requisition created, production logged, recipe created/updated/deleted).
  - Supervisor → Employee (requisition approved/rejected, purchase request decisions).
  - Supervisor → Inventory (approved requisitions ready for fulfillment).
  - Supervisor → Purchasing (approved purchase requests ready for PO creation).
  - Inventory → Employee (requisition ready for pickup; FEFO quarantine notifications).
  - Inventory → Supervisor (batch expiry alerts, RTV updates – if any).
  - Purchasing → Suppliers / internal users (if email or system notifications exist).

- For each notification block in legacy controllers:
  - Locate its new home DDD controller and confirm **type**, **priority**, **metadata** keys, and **action_url** are identical.

### 8.2 Audit Logs

- Make sure that any `AuditLog::create([...])` calls in `SupervisorController` or `PurchasingController` are:
  - Present in the equivalent DDD controller method.
  - Not duplicated (no double inserts).

### 8.3 Validation & Authorization

- Preserve all validation rules from monolithic methods when moving them.
- Check middleware on new controllers (mostly via route groups with `role:*` middleware) – ensure no open endpoints remain.

---

## 9. Final Cleanup (Phase X)

After Phases E, I, S, and P are completed:

1. **Search for class usages**:
   - Global search for `EmployeeController`, `InventoryController`, `SupervisorController`, `PurchasingController` across:
     - `routes/web.php`
     - `resources/views` (Blade)
     - `resources/js` (if front-end refers to controller routes by URL path)
     - Tests (if any).

2. **Confirm zero route bindings** to monolithic controllers.

3. **Delete monolithic controller files**:
   - `app/Http/Controllers/EmployeeController.php`
   - `app/Http/Controllers/InventoryController.php`
   - `app/Http/Controllers/SupervisorController.php`
   - `app/Http/Controllers/PurchasingController.php`

4. **Run full smoke test**:
   - Login as each role: admin, supervisor, inventory, purchasing, employee.
   - Walk through critical flows:
     - Requisition: employee → supervisor → inventory → employee.
     - Purchase request: inventory → supervisor → purchasing.
     - Receiving & RTV: purchasing → inventory.
     - Production flows: employee + supervisor analytics.

5. **Update documentation**:
   - Add a short note to `ADMIN_CONTROLLER_REFACTORING_STATUS.md` summarizing completion of Employee/Inventory/Supervisor/Purchasing refactor.
   - Optionally create a separate `DDD_Controller_Map.md` listing every route → controller → view.

---

## 10. Suggested Implementation Order

1. **Employee (Phase E)** – lowest blast radius, mostly UI/UX for employees.
2. **Inventory (Phase I)** – FEFO + dashboards + purchase requests.
3. **Purchasing (Phase P)** – back-office; depends on Inventory & Supervisor approvals.
4. **Supervisor (Phase S)** – approvals & dashboards, but do this after Inventory/Purchasing APIs are stable.
5. **Phase X** – cleanup and deletion of monolithic controllers.

This sequence keeps core approval and fulfillment flows functional while you incrementally move logic into the DDD controllers.
