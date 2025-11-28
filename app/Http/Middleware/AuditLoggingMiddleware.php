<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AuditLog;
use App\Services\AuditLogHelper;
use Illuminate\Support\Facades\Auth;

class AuditLoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only audit successful requests
        if ($response->isSuccessful()) {
            $this->auditRequest($request, $response);
        }

        return $response;
    }

    /**
     * Audit the current request
     */
    private function auditRequest(Request $request, Response $response): void
    {
        // Skip audit logging for certain routes
        if ($this->shouldSkipAudit($request)) {
            return;
        }

        try {
            // Extract route information
            $route = $request->route();
            $action = $request->method();
            $controller = $route ? $route->getActionName() : 'Unknown';

            // Only audit specific HTTP methods
            if (!in_array($action, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                return;
            }

            // Parse controller and method
            if (strpos($controller, '@') !== false) {
                [$controllerClass, $methodName] = explode('@', $controller);
                $tableName = $this->extractTableName($controllerClass, $methodName);
                
                if ($tableName) {
                    $this->createAuditLog($request, $tableName, $action);
                }
            }
        } catch (\Exception $e) {
            // Log audit failure but don't break the main request
            \Log::warning('Audit logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Extract table name from controller class and method
     */
    private function extractTableName(string $controllerClass, string $methodName): ?string
    {
        $methodMappings = [
            // Master Data
            'CategoryController@store' => 'categories',
            'CategoryController@update' => 'categories',
            'CategoryController@destroy' => 'categories',
            'CategoryController@toggleStatus' => 'categories',
            
            'ItemController@store' => 'items',
            'ItemController@update' => 'items',
            'ItemController@destroy' => 'items',
            'ItemController@toggleStatus' => 'items',
            
            'UnitController@store' => 'units',
            'UnitController@update' => 'units',
            'UnitController@destroy' => 'units',
            'UnitController@toggleStatus' => 'units',
            
            // Suppliers
            'SupplierController@storeSupplier' => 'suppliers',
            'SupplierController@updateSupplier' => 'suppliers',
            'SupplierController@destroySupplier' => 'suppliers',
            'SupplierController@toggleSupplierStatus' => 'suppliers',
            
            // Purchasing
            'PurchasingController@updateSupplierItemPrice' => 'supplier_items',
            'PurchasingController@bulkUpdateSupplierPrices' => 'supplier_items',
            
            // User Management
            'UserController@store' => 'users',
            'UserController@update' => 'users',
            'UserController@destroy' => 'users',
            'UserController@toggleStatus' => 'users',
            
            // Inventory Operations
            'InventoryController@receiveDelivery' => 'purchase_orders',
            'InventoryController@markAsReceived' => 'purchase_orders',
        ];

        $key = class_basename($controllerClass) . '@' . $methodName;
        return $methodMappings[$key] ?? null;
    }

    /**
     * Create audit log entry
     */
    private function createAuditLog(Request $request, string $tableName, string $action): void
    {
        // Determine record ID from request parameters
        $recordId = $this->extractRecordId($request);
        
        // Extract old and new values
        $oldValues = null;
        $newValues = null;

        if (in_array($action, ['PUT', 'PATCH'])) {
            // For updates, try to get old values from the database
            $model = $this->getModelInstance($tableName, $recordId);
            if ($model) {
                $oldValues = AuditLogHelper::extractOldValues($model);
            }
            $newValues = $this->extractNewValues($request);
        } elseif ($action === 'POST') {
            // For creates, only new values
            $newValues = $this->extractNewValues($request);
        } elseif ($action === 'DELETE') {
            // For deletes, only old values
            $model = $this->getModelInstance($tableName, $recordId);
            if ($model) {
                $oldValues = AuditLogHelper::extractOldValues($model);
            }
        }

        // Create audit log entry
        AuditLogHelper::log(
            $tableName,
            $recordId,
            $action,
            $oldValues,
            $newValues,
            $request
        );
    }

    /**
     * Extract record ID from request
     */
    private function extractRecordId(Request $request): int
    {
        // Try to get ID from route parameters
        $route = $request->route();
        if ($route && $route->parameterNames()) {
            foreach ($route->parameterNames() as $paramName) {
                $param = $route->parameter($paramName);
                if (is_numeric($param)) {
                    return (int) $param;
                }
            }
        }

        // Try to get ID from request data
        $idFields = ['id', 'record_id', 'item_id', 'category_id', 'supplier_id', 'unit_id'];
        foreach ($idFields as $field) {
            if ($request->has($field)) {
                return (int) $request->get($field);
            }
        }

        return 0; // No specific record ID found
    }

    /**
     * Get model instance for table
     */
    private function getModelInstance(string $tableName, int $recordId): ?object
    {
        $modelMappings = [
            'categories' => \App\Models\Category::class,
            'items' => \App\Models\Item::class,
            'units' => \App\Models\Unit::class,
            'suppliers' => \App\Models\Supplier::class,
            'users' => \App\Models\User::class,
            'supplier_items' => \App\Models\SupplierItem::class,
            'purchase_orders' => \App\Models\PurchaseOrder::class,
        ];

        $modelClass = $modelMappings[$tableName] ?? null;
        
        if ($modelClass && $recordId > 0) {
            return $modelClass::find($recordId);
        }

        return null;
    }

    /**
     * Extract new values from request
     */
    private function extractNewValues(Request $request): array
    {
        $values = $request->only([
            'name', 'description', 'status', 'is_active', 'is_preferred',
            'item_code', 'category_id', 'unit_id', 'min_stock_level', 'max_stock_level',
            'reorder_point', 'cost_price', 'selling_price', 'supplier_id', 'unit_price',
            'minimum_order_quantity', 'lead_time_days', 'rating', 'contact_person',
            'email', 'phone', 'mobile', 'address', 'city', 'province', 'postal_code',
            'tax_id', 'payment_terms', 'credit_limit', 'notes', 'username', 'email',
            'password', 'role', 'department', 'position', 'phone_number', 'emergency_contact'
        ]);

        // Remove sensitive fields
        unset($values['password']);

        return $values;
    }

    /**
     * Determine if audit should be skipped for this request
     */
    private function shouldSkipAudit(Request $request): bool
    {
        $skipRoutes = [
            '/login',
            '/logout',
            '/password/',
            '/profile',
            'debug',
            'test',
            'health',
            '_debugbar',
        ];

        $path = $request->path();
        foreach ($skipRoutes as $skipRoute) {
            if (str_contains($path, $skipRoute)) {
                return true;
            }
        }

        // Skip if user is not authenticated (except for login attempts)
        if (!Auth::check() && !str_contains($path, 'login')) {
            return true;
        }

        return false;
    }
}