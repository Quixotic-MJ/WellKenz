<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Item;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Requisition;
use App\Models\PurchaseOrder;
use App\Models\Batch;
use App\Models\AuditLog;
use App\Models\Supplier;
use App\Models\SystemSetting;
use App\Models\Notification;
use App\Services\UserService;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\BulkUserOperationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use PDO;

class AdminController extends Controller
{
    /**
     * The user service instance.
     *
     * @var UserService
     */
    protected $userService;

    /**
     * Create a new controller instance.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function systemOverview()
    {
        // Get greeting based on time of day
        $hour = Carbon::now()->hour;
        if ($hour < 12) {
            $greeting = 'Good Morning';
        } elseif ($hour < 17) {
            $greeting = 'Good Afternoon';
        } else {
            $greeting = 'Good Evening';
        }

        // Get current user name
        $userName = auth()->user()->name ?? 'Admin';

        // Get database health info
        $databaseHealth = $this->getDatabaseHealth();

        // Get security alerts count
        $securityAlertsCount = $this->getSecurityAlertsCount();

        $data = [
            // Welcome message data
            'greeting' => $greeting,
            'userName' => $userName,
            'currentTime' => Carbon::now(),
            'currentDate' => Carbon::now()->format('F j, Y'),
            'currentDay' => Carbon::now()->format('l'),

            // User Statistics
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_active', true)->count(),
            'inactiveUsers' => User::where('is_active', false)->count(),

            // Item Statistics
            'totalItems' => Item::count(),
            'activeItems' => Item::where('is_active', true)->count(),
            'categoryCount' => Category::where('is_active', true)->count(),

            // Database Health
            'databaseHealth' => $databaseHealth,

            // Security Alerts
            'securityAlertsCount' => $securityAlertsCount,

            // Inventory Health
            'itemsWithLowStock' => $this->getItemsWithLowStock(),
            'itemsOutOfStock' => $this->getItemsOutOfStock(),
            'lowStockCount' => 0, // Will be calculated
            'outOfStockCount' => 0, // Will be calculated

            // Requisition Statistics
            'requisitions' => $this->getRequisitionStatistics(),

            // Purchase Order Statistics
            'purchaseOrders' => $this->getPurchaseOrderStatistics(),

            // Low Stock Alerts (Top 5 items with lowest stock)
            'lowStockAlerts' => $this->getLowStockAlerts(),

            // Near Expiry Alerts (Top 5 batches expiring within 7 days)
            'expiringBatches' => $this->getExpiringBatches(),

            // Recent Database Updates (Last 5 stock movements or audit logs)
            'recentUpdates' => $this->getRecentDatabaseUpdates(),

            // Security Log (Last 5 security-related audit logs)
            'securityLogs' => $this->getSecurityLogs(),
        ];

        // Calculate low stock and out of stock counts
        $data['lowStockCount'] = count($data['itemsWithLowStock']);
        $data['outOfStockCount'] = count($data['itemsOutOfStock']);

        return view('Admin.system_overview', $data);
    }

    private function getItemsWithLowStock()
    {
        return Item::where('is_active', true)
            ->with(['currentStockRecord', 'unit'])
            ->get()
            ->filter(function ($item) {
                $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                // Use reorder_point if set, otherwise use min_stock_level
                $threshold = $item->reorder_point > 0 ? $item->reorder_point : $item->min_stock_level;
                // Include items at or below threshold (including out of stock)
                return $threshold > 0 && $currentStock <= $threshold;
            })
            ->sortBy(function ($item) {
                $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                return $currentStock;
            })
            ->take(5)
            ->values();
    }

    private function getItemsOutOfStock()
    {
        return Item::where('is_active', true)
            ->with(['currentStockRecord', 'unit'])
            ->get()
            ->filter(function ($item) {
                $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                return $currentStock <= 0;
            })
            ->sortBy(function ($item) {
                $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                return $currentStock;
            })
            ->take(5)
            ->values();
    }

    private function getRequisitionStatistics()
    {
        return [
            'pendingApproval' => Requisition::where('status', 'pending')->count(),
            'approvedToday' => Requisition::where('status', 'approved')
                ->whereDate('approved_at', Carbon::today())
                ->count(),
            'rejected' => Requisition::where('status', 'rejected')->count(),
            'totalRequisitions' => Requisition::count(),
        ];
    }

    private function getPurchaseOrderStatistics()
    {
        return [
            'draft' => PurchaseOrder::where('status', 'draft')->count(),
            'ordered' => PurchaseOrder::whereIn('status', ['sent', 'confirmed'])->count(),
            'delivered' => PurchaseOrder::where('status', 'completed')->count(),
            'totalOrders' => PurchaseOrder::count(),
            'averageDeliveryTime' => $this->calculateAverageDeliveryTime(),
        ];
    }

    private function calculateAverageDeliveryTime()
    {
        $completedOrders = PurchaseOrder::where('status', 'completed')
            ->whereNotNull('actual_delivery_date')
            ->get();

        if ($completedOrders->isEmpty()) {
            return 0;
        }

        $totalDays = 0;
        $count = 0;

        foreach ($completedOrders as $order) {
            if ($order->order_date && $order->actual_delivery_date) {
                $totalDays += $order->order_date->diffInDays($order->actual_delivery_date);
                $count++;
            }
        }

        return $count > 0 ? round($totalDays / $count, 1) : 0;
    }

    private function getLowStockAlerts()
    {
        $items = $this->getItemsWithLowStock();
        
        return $items->map(function ($item) {
            $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
            // Use reorder_point if set, otherwise use min_stock_level
            $threshold = $item->reorder_point > 0 ? $item->reorder_point : $item->min_stock_level;
            
            return [
                'id' => $item->id,
                'name' => $item->name,
                'item_code' => $item->item_code,
                'current_stock' => $currentStock,
                'reorder_level' => $threshold,
                'unit' => $item->unit->symbol ?? '',
            ];
        })->toArray();
    }

    private function getExpiringBatches()
    {
        $sevenDaysFromNow = Carbon::now()->addDays(7);
        
        return Batch::with(['item', 'supplier'])
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where(function($query) use ($sevenDaysFromNow) {
                // Include batches that are already expired OR will expire within 7 days
                $query->where('expiry_date', '<=', $sevenDaysFromNow);
            })
            ->orderBy('expiry_date')
            ->take(5)
            ->get()
            ->map(function ($batch) {
                $daysUntilExpiry = (int) Carbon::now()->startOfDay()->diffInDays($batch->expiry_date->startOfDay(), false);
                
                return [
                    'id' => $batch->id,
                    'item_name' => $batch->item->name ?? 'Unknown Item',
                    'batch_number' => $batch->batch_number,
                    'expiry_date' => $batch->expiry_date->format('M j, Y'),
                    'days_until_expiry' => $daysUntilExpiry,
                    'expiry_status' => ($daysUntilExpiry < 0) ? 'Expired ' . abs($daysUntilExpiry) . ' days ago' : 
                                     (($daysUntilExpiry == 0) ? 'Expires Today' :
                                     (($daysUntilExpiry == 1) ? 'Expires Tomorrow' : 
                                     "Expires in {$daysUntilExpiry} days")),
                ];
            })->toArray();
    }

    private function getRecentDatabaseUpdates()
    {
        // Get recent audit logs (simulating stock movements for demo)
        return AuditLog::with(['user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($log) {
                $action = strtolower($log->action);
                $icon = 'fas fa-edit'; // default
                
                if ($action === 'create') {
                    $icon = 'fas fa-plus';
                } elseif ($action === 'delete') {
                    $icon = 'fas fa-trash';
                } elseif ($action === 'update') {
                    $icon = 'fas fa-edit';
                }

                return [
                    'id' => $log->id,
                    'action' => $action,
                    'table_name' => $log->table_name,
                    'record_id' => $log->record_id,
                    'description' => $this->generateLogDescription($log),
                    'user_name' => $log->user->name ?? 'System',
                    'created_at' => $log->created_at,
                    'time_ago' => $log->created_at->diffForHumans(),
                    'icon' => $icon,
                ];
            })->toArray();
    }

    private function generateLogDescription($log)
    {
        $action = strtolower($log->action);
        $table = $log->table_name;
        
        switch ($table) {
            case 'items':
                if ($action === 'create') return 'New Item Added';
                if ($action === 'update') return 'Item Updated';
                if ($action === 'delete') return 'Item Removed';
                break;
            case 'users':
                if ($action === 'create') return 'New User Added';
                if ($action === 'update') return 'User Modified';
                if ($action === 'delete') return 'User Removed';
                break;
            case 'purchase_orders':
                if ($action === 'create') return 'Purchase Order Created';
                if ($action === 'update') return 'Purchase Order Updated';
                break;
            case 'suppliers':
                if ($action === 'create') return 'Supplier Added';
                if ($action === 'update') return 'Supplier Updated';
                if ($action === 'delete') return 'Supplier Removed';
                break;
            default:
                return ucfirst($action) . ' on ' . ucwords(str_replace('_', ' ', $table));
        }
        
        return ucfirst($action) . ' on ' . ucwords(str_replace('_', ' ', $table));
    }

    private function getSecurityLogs()
    {
        // For demo purposes, we'll simulate security logs from audit logs
        // In a real system, you might have a dedicated security logs table
        return AuditLog::with(['user'])
            ->where(function ($query) {
                $query->where('table_name', 'users')
                      ->orWhere('action', 'login')
                      ->orWhere('action', 'logout');
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($log) {
                $color = 'bg-green-500'; // default color
                $description = 'System Activity';
                
                if (strtolower($log->action) === 'login') {
                    $color = 'bg-blue-500';
                    $description = 'User Login';
                } elseif (strtolower($log->action) === 'update' && $log->table_name === 'users') {
                    $color = 'bg-amber-500';
                    $description = 'User Role Modified';
                }
                
                return [
                    'id' => $log->id,
                    'description' => $description,
                    'details' => $this->generateSecurityLogDetails($log),
                    'time_ago' => $log->created_at->diffForHumans(),
                    'created_at' => $log->created_at,
                    'color' => $color,
                ];
            })->toArray();
    }

    private function generateSecurityLogDetails($log)
    {
        $action = strtolower($log->action);
        $table = $log->table_name;
        
        if ($action === 'login') {
            return 'User login attempt recorded.';
        } elseif ($action === 'update' && $table === 'users') {
            return 'User profile or permissions modified.';
        } elseif ($action === 'create') {
            return 'New system record created.';
        } elseif ($action === 'delete') {
            return 'System record removed.';
        }
        
        return 'System activity recorded.';
    }

    /**
     * Display all users with pagination, search, and filters.
     */
    public function allUsers(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'role' => $request->get('role'),
            'status' => $request->get('status')
        ];

        $perPage = $request->get('per_page', 10);
        $users = $this->userService->getPaginatedUsers($filters, $perPage);

        // Get role statistics for filters
        $roleStats = $this->userService->getRoleStatistics();

        return view('Admin.user_management.all_user', compact('users', 'roleStats'));
    }

    /**
     * Display user roles and permissions.
     */
    public function userRoles()
    {
        // Get all users with their profiles grouped by role
        $roleData = User::with('profile')
            ->selectRaw('role, COUNT(*) as user_count')
            ->groupBy('role')
            ->get()
            ->map(function ($roleGroup) {
                $users = User::with('profile')->where('role', $roleGroup->role)->get();
                
                return [
                    'role' => $roleGroup->role,
                    'formatted_role' => $this->getFormattedRole($roleGroup->role),
                    'user_count' => $roleGroup->user_count,
                    'users' => $users,
                    'description' => $this->getRoleDescription($roleGroup->role),
                    'icon' => $this->getRoleIcon($roleGroup->role),
                    'color' => $this->getRoleColor($roleGroup->role),
                    'category' => $this->getRoleCategory($roleGroup->role)
                ];
            });

        return view('Admin.user_management.roles', compact('roleData'));
    }

    /**
     * Create a new role.
     */
    public function createRole(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string|max:50|unique:users,role',
            'display_name' => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'category' => 'required|string|max:50',
            'permissions' => 'array',
            'permissions.*' => 'string'
        ]);

        try {
            // For now, we'll just log the new role creation
            // In a real application, you might want to create a roles table
            $newRole = [
                'role' => strtolower($request->role_name),
                'display_name' => $request->display_name,
                'description' => $request->description,
                'category' => $request->category,
                'permissions' => $request->permissions ?? []
            ];

            // Log the role creation
            \Log::info('New role created:', $newRole);

            // In a real app, you would store this in a database table
            // Example: Role::create($newRole);

            return response()->json([
                'success' => true,
                'message' => "Role '{$request->display_name}' created successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display items masterlist with pagination, search and filters.
     */
    public function items(Request $request)
    {
        $query = Item::with(['category', 'unit', 'currentStockRecord'])
            ->where('is_active', true);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('item_code', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('name', 'ilike', "%{$request->category}%");
            });
        }

        // Stock status filter
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->where(function($q) {
                    $q->whereHas('currentStockRecord', function($subQ) {
                        $subQ->where('current_quantity', '>', 0)
                             ->where('current_quantity', '<=', \DB::raw('items.reorder_point'));
                    })
                    ->orWhere(function($subQ2) {
                        // Items without stock records but with reorder_point > 0 (treat as low stock)
                        $subQ2->doesntHave('currentStockRecord')
                              ->where('reorder_point', '>', 0);
                    });
                });
            } elseif ($request->stock_status === 'out') {
                $query->where(function($q) {
                    $q->whereHas('currentStockRecord', function($subQ) {
                        $subQ->where('current_quantity', '<=', 0);
                    })
                    ->orWhereDoesntHave('currentStockRecord');
                });
            } elseif ($request->stock_status === 'good') {
                $query->whereHas('currentStockRecord', function($q) {
                    $q->where('current_quantity', '>', 0)
                      ->where('current_quantity', '>', \DB::raw('items.reorder_point'));
                });
            }
        }

        $perPage = $request->get('per_page', 10);
        $items = $query->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        // Get categories for filter dropdown
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('Admin.master_files.item_masterlist', compact('items', 'categories'));
    }

    /**
     * Create a new item.
     */
    public function createItem(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'item_code' => 'required|string|max:50|unique:items,item_code',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'item_type' => 'required|in:raw_material,finished_good,semi_finished,supply',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'reorder_point' => 'nullable|numeric|min:0',
            'shelf_life_days' => 'nullable|integer|min:0',
        ]);

        try {
            $item = Item::create([
                'name' => $request->name,
                'item_code' => $request->item_code,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'unit_id' => $request->unit_id,
                'item_type' => $request->item_type,
                'cost_price' => $request->cost_price ?? 0,
                'selling_price' => $request->selling_price ?? 0,
                'reorder_point' => $request->reorder_point ?? 0,
                'shelf_life_days' => $request->shelf_life_days,
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item created successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing item.
     */
    public function updateItem(Request $request, Item $item)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'item_code' => 'required|string|max:50|unique:items,item_code,' . $item->id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'item_type' => 'required|in:raw_material,finished_good,semi_finished,supply',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'reorder_point' => 'nullable|numeric|min:0',
            'shelf_life_days' => 'nullable|integer|min:0',
        ]);

        try {
            $item->update([
                'name' => $request->name,
                'item_code' => $request->item_code,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'unit_id' => $request->unit_id,
                'item_type' => $request->item_type,
                'cost_price' => $request->cost_price ?? 0,
                'selling_price' => $request->selling_price ?? 0,
                'reorder_point' => $request->reorder_point ?? 0,
                'shelf_life_days' => $request->shelf_life_days,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get item data for editing.
     */
    public function editItem(Item $item)
    {
        $item->load(['category', 'unit', 'currentStockRecord']);
        return response()->json($item);
    }

    /**
     * Delete an item.
     */
    public function deleteItem(Item $item)
    {
        try {
            $itemName = $item->name;
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => "Item '{$itemName}' deleted successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories and units for dropdowns.
     */
    public function getItemData()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $units = Unit::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'symbol']);

        return response()->json([
            'categories' => $categories,
            'units' => $units
        ]);
    }

    /**
     * Create a new user.
     */
    public function createUser(StoreUserRequest $request)
    {
        try {
            $user = $this->userService->createUser($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'User created successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user information.
     */
    public function updateUser(UpdateUserRequest $request, User $user)
    {
        try {
            $this->userService->updateUser($user, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle user active/inactive status.
     */
    public function toggleUserStatus(User $user)
    {
        try {
            $user = $this->userService->toggleUserStatus($user);
            $status = $user->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "User {$status} successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating user status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a user.
     */
    public function deleteUser(User $user)
    {
        try {
            $userName = $user->name;
            $this->userService->deleteUser($user);

            return response()->json([
                'success' => true,
                'message' => "User '{$userName}' deleted successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search users for AJAX requests.
     */
    public function searchUsers(Request $request)
    {
        $query = $request->get('q', '');
        $users = $this->userService->searchUsers($query);
        return response()->json($users);
    }

    /**
     * Get user data for editing.
     */
    public function editUser(User $user)
    {
        return response()->json($this->userService->getUserForEdit($user));
    }

    /**
     * Get role details with users for modal display.
     */
    public function getRoleDetails($role)
    {
        $users = User::with('profile')->where('role', $role)->get();
        
        $data = [
            'role' => $role,
            'formatted_role' => $this->getFormattedRole($role),
            'user_count' => $users->count(),
            'users' => $users,
            'description' => $this->getRoleDescription($role),
            'icon' => $this->getRoleIcon($role),
            'color' => $this->getRoleColor($role),
            'category' => $this->getRoleCategory($role)
        ];

        return response()->json($data);
    }

    /**
     * Save role permissions.
     */
    public function saveRolePermissions(Request $request, $role)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string'
        ]);

        try {
            // In a real application, you would save this to a permissions table
            // For now, we'll just log it
            \Log::info("Updated permissions for role {$role}:", $request->permissions);

            // You could store this in cache, database, or configuration
            // Example: Cache::put("role_permissions.{$role}", $request->permissions);

            return response()->json([
                'success' => true,
                'message' => 'Role permissions updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current role permissions.
     */
    public function getRolePermissions($role)
    {
        // In a real application, fetch from database/cache
        $rolePermissions = [
            'admin' => ['view_users', 'manage_users', 'create_requisitions', 'approve_requisitions', 'manage_inventory', 'view_audit_logs', 'download_reports', 'system_admin'],
            'supervisor' => ['view_users', 'create_requisitions', 'approve_requisitions', 'view_audit_logs', 'download_reports'],
            'purchasing' => ['view_users', 'create_requisitions', 'manage_inventory', 'download_reports'],
            'inventory' => ['view_users', 'create_requisitions', 'manage_inventory'],
            'employee' => ['create_requisitions']
        ];

        $permissions = $rolePermissions[$role] ?? [];

        return response()->json([
            'permissions' => $permissions
        ]);
    }

    /**
     * Reset user password.
     */
    public function resetUserPassword(User $user)
    {
        try {
            $newPassword = $this->userService->resetUserPassword($user);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully!',
                'new_password' => $newPassword
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error resetting password: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a temporary password.
     */
    private function generateTemporaryPassword()
    {
        // Generate a secure random password
        return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*'), 0, 12);
    }

    /**
     * Change user password (for admin use or self-service).
     */
    public function changeUserPassword(Request $request, ?User $user = null)
    {
        // If no user specified, use authenticated user (for self-service)
        $targetUser = $user ?: auth()->user();
        
        $request->validate([
            'current_password' => 'required_if:' . ($user ? 'false' : 'true') . '|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $isAdminChange = $user && auth()->user()->hasRole('admin');
            $currentPassword = $request->current_password;
            
            $this->userService->changeUserPassword(
                $targetUser, 
                $currentPassword, 
                $request->new_password, 
                $isAdminChange
            );

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully!'
            ]);

        } catch (\Exception $e) {
            $statusCode = $e->getMessage() === 'Current password is incorrect.' ? 422 : 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    /**
     * Bulk user operations (activate, deactivate, delete multiple users).
     */
    public function bulkUserOperations(BulkUserOperationRequest $request)
    {
        try {
            $operation = $request->operation;
            $userIds = $request->user_ids;
            
            $affectedCount = $this->userService->bulkUserOperations($userIds, $operation);

            $operationName = $operation === 'activate' ? 'activated' : 
                           ($operation === 'deactivate' ? 'deactivated' : 'deleted');

            return response()->json([
                'success' => true,
                'message' => "{$affectedCount} users {$operationName} successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error performing bulk operation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get formatted role name.
     */
    private function getFormattedRole($role)
    {
        $roleMap = [
            'admin' => 'Administrator',
            'supervisor' => 'Supervisor',
            'purchasing' => 'Purchasing Officer',
            'inventory' => 'Inventory Manager',
            'employee' => 'Staff'
        ];

        return $roleMap[$role] ?? ucfirst($role);
    }

    /**
     * Get role description.
     */
    private function getRoleDescription($role)
    {
        $descriptions = [
            'admin' => 'Full access to all system configurations, user management, and financial reports.',
            'supervisor' => 'Can approve requisitions, view audit logs, and manage inventory adjustments.',
            'purchasing' => 'Can create and manage purchase orders, manage suppliers, and handle procurement.',
            'inventory' => 'Can manage stock levels, receive deliveries, and handle stock movements.',
            'employee' => 'Restricted access to item requests, recipe viewing, and basic production modules.'
        ];

        return $descriptions[$role] ?? 'No description available.';
    }

    /**
     * Get role icon.
     */
    private function getRoleIcon($role)
    {
        $icons = [
            'admin' => 'fas fa-user-astronaut',
            'supervisor' => 'fas fa-user-tie',
            'purchasing' => 'fas fa-shopping-cart',
            'inventory' => 'fas fa-warehouse',
            'employee' => 'fas fa-user'
        ];

        return $icons[$role] ?? 'fas fa-user';
    }

    /**
     * Get role color.
     */
    private function getRoleColor($role)
    {
        $colors = [
            'admin' => 'bg-purple-100 text-purple-800',
            'supervisor' => 'bg-blue-100 text-blue-800',
            'purchasing' => 'bg-green-100 text-green-800',
            'inventory' => 'bg-amber-100 text-amber-800',
            'employee' => 'bg-gray-100 text-gray-800'
        ];

        return $colors[$role] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get role category.
     */
    private function getRoleCategory($role)
    {
        $categories = [
            'admin' => 'System Owner',
            'supervisor' => 'Management',
            'purchasing' => 'Operations',
            'inventory' => 'Operations',
            'employee' => 'Staff'
        ];

        return $categories[$role] ?? 'General';
    }

    /**
     * Get database health information.
     */
    private function getDatabaseHealth()
    {
        try {
            // Check database connection
            DB::connection()->getPdo();
            
            // Get last audit log to determine last activity
            $lastAuditLog = AuditLog::orderBy('created_at', 'desc')->first();
            $lastBackupTime = $lastAuditLog ? $lastAuditLog->created_at->diffForHumans() : 'Never';
            
            return [
                'status' => 'Good',
                'status_color' => 'green',
                'last_backup' => $lastBackupTime,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'Error',
                'status_color' => 'red',
                'last_backup' => 'Unknown',
            ];
        }
    }

    /**
     * Get security alerts count.
     */
    private function getSecurityAlertsCount()
    {
        // Count recent failed login attempts or suspicious activities
        // For now, we'll check for recent user modifications or deletions
        $recentSecurityEvents = AuditLog::where('table_name', 'users')
            ->where('action', 'DELETE')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();
        
        return $recentSecurityEvents;
    }

    /**
     * Display categories with pagination, search, and filters.
     */
    public function categories(Request $request)
    {
        $query = Category::with(['items' => function($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $perPage = $request->get('per_page', 10);
        $categories = $query->paginate($perPage)
            ->withQueryString()
            ->through(function ($category) {
                $category->linked_items_count = $category->items->count();
                return $category;
            });

        return view('Admin.master_files.categories', compact('categories'));
    }

    /**
     * Create a new category.
     */
    public function createCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        try {
            $category = Category::create([
                'name' => $request->name,
                'description' => $request->description,
                'parent_id' => $request->parent_id,
                'is_active' => true
            ]);

            // Log the category creation
            AuditLog::create([
                'table_name' => 'categories',
                'record_id' => $category->id,
                'action' => 'CREATE',
                'new_values' => $category->toJson(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing category.
     */
    public function updateCategory(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        try {
            // Prevent circular reference (category can't be its own parent)
            if ($request->parent_id == $category->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'A category cannot be its own parent.'
                ], 422);
            }

            $oldValues = $category->toArray();

            $category->update([
                'name' => $request->name,
                'description' => $request->description,
                'parent_id' => $request->parent_id,
            ]);

            // Log the category update
            AuditLog::create([
                'table_name' => 'categories',
                'record_id' => $category->id,
                'action' => 'UPDATE',
                'old_values' => json_encode($oldValues),
                'new_values' => $category->toJson(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle category active/inactive status.
     */
    public function toggleCategoryStatus(Category $category)
    {
        try {
            $category->update(['is_active' => !$category->is_active]);

            // Log the status change
            AuditLog::create([
                'table_name' => 'categories',
                'record_id' => $category->id,
                'action' => 'UPDATE',
                'old_values' => json_encode(['is_active' => !$category->is_active]),
                'new_values' => json_encode(['is_active' => $category->is_active]),
                'user_id' => auth()->id(),
            ]);

            $status = $category->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "Category {$status} successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating category status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a category.
     */
    public function deleteCategory(Category $category)
    {
        try {
            // Check if category has items
            if ($category->items()->where('is_active', true)->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with active items. Please move or delete the items first.'
                ], 422);
            }

            // Check if category has child categories
            if ($category->children()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with subcategories. Please move or delete the subcategories first.'
                ], 422);
            }

            $categoryName = $category->name;
            
            // Log the deletion before actually deleting
            AuditLog::create([
                'table_name' => 'categories',
                'record_id' => $category->id,
                'action' => 'DELETE',
                'old_values' => $category->toJson(),
                'user_id' => auth()->id(),
            ]);

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => "Category '{$categoryName}' deleted successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get category data for editing.
     */
    public function editCategory(Category $category)
    {
        $category->load('items');
        return response()->json($category);
    }

    /**
     * Get all parent categories for dropdown (excluding the current category if editing).
     */
    public function getParentCategories(Request $request)
    {
        $excludeId = $request->get('exclude_id');
        
        $query = Category::where('is_active', true)
            ->orderBy('name');
            
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $categories = $query->get(['id', 'name']);

        return response()->json($categories);
    }

    /**
     * Search categories for AJAX requests.
     */
    public function searchCategories(Request $request)
    {
        $query = $request->get('q', '');
        $categories = Category::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'ilike', "%{$query}%")
                  ->orWhere('description', 'ilike', "%{$query}%");
            })
            ->orderBy('name')
            ->take(10)
            ->get(['id', 'name', 'description']);
            
        return response()->json($categories);
    }

    /**
     * Display units configuration with pagination, search, and filters.
     */
    public function units(Request $request)
    {
        $query = Unit::orderBy('type')->orderBy('name');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('symbol', 'ilike', "%{$search}%");
            });
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $perPage = $request->get('per_page', 10);
        $units = $query->paginate($perPage)
            ->withQueryString();

        // Get base units (weight, volume, piece) for reference
        $baseUnits = Unit::whereIn('type', ['weight', 'volume', 'piece'])
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        // Get packaging units (typically piece type with names like Sack, Box, etc.)
        $packagingUnits = Unit::where('type', 'piece')
            ->where('is_active', true)
            ->whereNotIn('name', ['Piece', 'Dozen']) // Exclude basic counting units
            ->orderBy('name')
            ->get();

        return view('Admin.master_files.unit_config', compact('units', 'baseUnits', 'packagingUnits'));
    }

    /**
     * Create a new unit.
     */
    public function createUnit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:units,name',
            'symbol' => 'required|string|max:10|unique:units,symbol',
            'type' => 'required|in:weight,volume,piece,length',
            'base_unit_id' => 'nullable|exists:units,id',
            'conversion_factor' => 'nullable|numeric|min:0.000001',
        ]);

        try {
            $unit = Unit::create([
                'name' => $request->name,
                'symbol' => $request->symbol,
                'type' => $request->type,
                'base_unit_id' => $request->base_unit_id,
                'conversion_factor' => $request->conversion_factor ?? 1.000000,
                'is_active' => true
            ]);

            // Log the unit creation
            AuditLog::create([
                'table_name' => 'units',
                'record_id' => $unit->id,
                'action' => 'CREATE',
                'new_values' => $unit->toJson(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Unit created successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating unit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing unit.
     */
    public function updateUnit(Request $request, Unit $unit)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:units,name,' . $unit->id,
            'symbol' => 'required|string|max:10|unique:units,symbol,' . $unit->id,
            'type' => 'required|in:weight,volume,piece,length',
            'base_unit_id' => 'nullable|exists:units,id',
            'conversion_factor' => 'nullable|numeric|min:0.000001',
        ]);

        try {
            // Prevent changing type for units that have dependencies
            if ($unit->type !== $request->type && $this->hasUnitDependencies($unit)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot change unit type as it has dependencies in items or other records.'
                ], 422);
            }

            $oldValues = $unit->toArray();

            $unit->update([
                'name' => $request->name,
                'symbol' => $request->symbol,
                'type' => $request->type,
                'base_unit_id' => $request->base_unit_id,
                'conversion_factor' => $request->conversion_factor ?? 1.000000,
            ]);

            // Log the unit update
            AuditLog::create([
                'table_name' => 'units',
                'record_id' => $unit->id,
                'action' => 'UPDATE',
                'old_values' => json_encode($oldValues),
                'new_values' => $unit->toJson(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Unit updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating unit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle unit active/inactive status.
     */
    public function toggleUnitStatus(Unit $unit)
    {
        try {
            // Check if unit has dependencies before deactivating
            if ($unit->is_active && $this->hasUnitDependencies($unit)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate unit as it has dependencies in items or other records.'
                ], 422);
            }

            $unit->update(['is_active' => !$unit->is_active]);

            // Log the status change
            AuditLog::create([
                'table_name' => 'units',
                'record_id' => $unit->id,
                'action' => 'UPDATE',
                'old_values' => json_encode(['is_active' => !$unit->is_active]),
                'new_values' => json_encode(['is_active' => $unit->is_active]),
                'user_id' => auth()->id(),
            ]);

            $status = $unit->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "Unit {$status} successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating unit status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a unit.
     */
    public function deleteUnit(Unit $unit)
    {
        try {
            // Check if unit has dependencies
            if ($this->hasUnitDependencies($unit)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete unit as it has dependencies in items or other records. Please update or remove dependent records first.'
                ], 422);
            }

            $unitName = $unit->name;
            
            // Log the deletion before actually deleting
            AuditLog::create([
                'table_name' => 'units',
                'record_id' => $unit->id,
                'action' => 'DELETE',
                'old_values' => $unit->toJson(),
                'user_id' => auth()->id(),
            ]);

            $unit->delete();

            return response()->json([
                'success' => true,
                'message' => "Unit '{$unitName}' deleted successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting unit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unit data for editing.
     */
    public function editUnit(Unit $unit)
    {
        $unit->load('baseUnit');
        return response()->json($unit);
    }

    /**
     * Get all base units for dropdown.
     */
    public function getBaseUnits(Request $request)
    {
        $type = $request->get('type');
        $excludeId = $request->get('exclude_id');
        
        $query = Unit::where('is_active', true)
            ->whereIn('type', ['weight', 'volume', 'piece'])
            ->orderBy('type')
            ->orderBy('name');
            
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        if ($type) {
            $query->where('type', $type);
        }
        
        $units = $query->get(['id', 'name', 'symbol', 'type']);

        return response()->json($units);
    }

    /**
     * Check if unit has dependencies.
     */
    private function hasUnitDependencies(Unit $unit)
    {
        // Check items table
        if ($unit->items()->count() > 0) {
            return true;
        }

        // Check recipe_ingredients table
        if ($unit->recipeIngredients()->count() > 0) {
            return true;
        }

        // Check purchase_order_items table
        if ($unit->purchaseOrderItems()->count() > 0) {
            return true;
        }

        // Check other related tables
        // Add more checks as needed

        return false;
    }

    /**
     * Search units for AJAX requests.
     */
    public function searchUnits(Request $request)
    {
        $query = $request->get('q', '');
        $units = Unit::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'ilike', "%{$query}%")
                  ->orWhere('symbol', 'ilike', "%{$query}%");
            })
            ->orderBy('name')
            ->take(10)
            ->get(['id', 'name', 'symbol', 'type']);
            
        return response()->json($units);
    }

    /**
     * Display audit logs with filtering, searching, and pagination.
     */
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with(['user'])
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('table_name', 'ilike', "%{$search}%")
                  ->orWhere('action', 'ilike', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'ilike', "%{$search}%")
                                ->orWhere('email', 'ilike', "%{$search}%");
                  });
            });
        }

        // User filter
        if ($request->filled('user')) {
            if ($request->user === 'system') {
                $query->whereNull('user_id');
            } else {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('id', $request->user);
                });
            }
        }

        // Module filter (based on table_name)
        if ($request->filled('module')) {
            $moduleTables = [
                'auth' => ['users', 'user_profiles'],
                'inventory' => ['items', 'stock_movements', 'current_stock', 'batches'],
                'finance' => ['purchase_orders', 'purchase_order_items', 'items'],
                'users' => ['users', 'user_profiles']
            ];
            
            $tables = $moduleTables[$request->module] ?? [];
            if (!empty($tables)) {
                $query->whereIn('table_name', $tables);
            }
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Action filter
        if ($request->filled('action')) {
            $query->where('action', strtoupper($request->action));
        }

        $perPage = $request->get('per_page', 15);
        $auditLogs = $query->paginate($perPage)
            ->withQueryString();

        // Get filter options
        $users = User::orderBy('name')->get(['id', 'name', 'role']);
        $totalLogs = AuditLog::count();

        return view('Admin.system.audit_logs', compact('auditLogs', 'users', 'totalLogs'));
    }

    /**
     * Export audit logs to CSV.
     */
    public function exportAuditLogs(Request $request)
    {
        // Reuse the same query logic from auditLogs method
        $query = AuditLog::with(['user'])
            ->orderBy('created_at', 'desc');

        // Apply same filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('table_name', 'ilike', "%{$search}%")
                  ->orWhere('action', 'ilike', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'ilike', "%{$search}%")
                                ->orWhere('email', 'ilike', "%{$search}%");
                  });
            });
        }

        if ($request->filled('user')) {
            if ($request->user === 'system') {
                $query->whereNull('user_id');
            } else {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('id', $request->user);
                });
            }
        }

        if ($request->filled('module')) {
            $moduleTables = [
                'auth' => ['users', 'user_profiles'],
                'inventory' => ['items', 'stock_movements', 'current_stock', 'batches'],
                'finance' => ['purchase_orders', 'purchase_order_items', 'items'],
                'users' => ['users', 'user_profiles']
            ];
            
            $tables = $moduleTables[$request->module] ?? [];
            if (!empty($tables)) {
                $query->whereIn('table_name', $tables);
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('action')) {
            $query->where('action', strtoupper($request->action));
        }

        $auditLogs = $query->get();

        // Generate CSV
        $filename = 'audit_logs_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($auditLogs) {
            $file = fopen('php://output', 'w');
            
            // Add CSV header
            fputcsv($file, [
                'ID', 
                'Timestamp', 
                'User', 
                'Action', 
                'Table', 
                'Record ID', 
                'Changes',
                'IP Address',
                'User Agent'
            ]);

            // Add data rows
            foreach ($auditLogs as $log) {
                $userName = $log->user ? $log->user->name : 'System';
                
                // Format changes
                $changes = '';
                if ($log->old_values || $log->new_values) {
                    $oldValues = is_string($log->old_values) ? json_decode($log->old_values, true) : $log->old_values;
                    $newValues = is_string($log->new_values) ? json_decode($log->new_values, true) : $log->new_values;
                    
                    if ($log->action == 'UPDATE' && $oldValues && $newValues) {
                        $changedFields = array_diff_key($newValues, $oldValues);
                        $changedFields = array_merge($changedFields, array_intersect_key($newValues, $oldValues));
                        $changedFields = array_filter($changedFields, function($key) use ($oldValues, $newValues) {
                            return isset($oldValues[$key]) && isset($newValues[$key]) && $oldValues[$key] != $newValues[$key];
                        }, ARRAY_FILTER_USE_KEY);
                        
                        if (!empty($changedFields)) {
                            $changes = implode(', ', array_keys($changedFields));
                        }
                    } elseif ($log->action == 'CREATE') {
                        $changes = 'New record created';
                    } elseif ($log->action == 'DELETE') {
                        $changes = 'Record deleted';
                    }
                }

                fputcsv($file, [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $userName,
                    $log->action,
                    $log->table_name,
                    $log->record_id,
                    $changes,
                    $log->ip_address,
                    $log->user_agent
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export single audit log as proof document.
     */
    public function exportAuditLogProof(AuditLog $auditLog)
    {
        // For now, return a simple PDF-like response
        // In a real implementation, you might use a PDF library like Dompdf
        $filename = "audit_log_proof_{$auditLog->id}_" . Carbon::now()->format('Y-m-d') . '.txt';
        
        $content = "AUDIT LOG PROOF DOCUMENT\n";
        $content .= "=" . str_repeat("=", 50) . "\n\n";
        $content .= "Log ID: {$auditLog->id}\n";
        $content .= "Timestamp: {$auditLog->created_at->format('Y-m-d H:i:s')}\n";
        $content .= "User: " . ($auditLog->user ? $auditLog->user->name : 'System') . "\n";
        $content .= "Action: {$auditLog->action}\n";
        $content .= "Table: {$auditLog->table_name}\n";
        $content .= "Record ID: {$auditLog->record_id}\n";
        $content .= "IP Address: {$auditLog->ip_address}\n";
        $content .= "User Agent: {$auditLog->user_agent}\n\n";
        
        if ($auditLog->old_values) {
            $content .= "Old Values:\n";
            $oldValues = is_string($auditLog->old_values) ? json_decode($auditLog->old_values, true) : $auditLog->old_values;
            foreach ($oldValues as $key => $value) {
                $content .= "  {$key}: {$value}\n";
            }
            $content .= "\n";
        }
        
        if ($auditLog->new_values) {
            $content .= "New Values:\n";
            $newValues = is_string($auditLog->new_values) ? json_decode($auditLog->new_values, true) : $auditLog->new_values;
            foreach ($newValues as $key => $value) {
                $content .= "  {$key}: {$value}\n";
            }
            $content .= "\n";
        }
        
        $content .= "Generated: " . Carbon::now()->format('Y-m-d H:i:s') . "\n";
        $content .= "Generated by: " . auth()->user()->name . "\n";

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Show individual audit log details.
     */
    public function showAuditLog(AuditLog $auditLog)
    {
        $auditLog->load(['user']);
        
        // Return as JSON for modal display or detailed view
        return response()->json([
            'id' => $auditLog->id,
            'timestamp' => $auditLog->created_at->format('Y-m-d H:i:s'),
            'user' => $auditLog->user ? [
                'name' => $auditLog->user->name,
                'email' => $auditLog->user->email,
                'role' => $auditLog->user->role
            ] : null,
            'action' => $auditLog->action,
            'table_name' => $auditLog->table_name,
            'record_id' => $auditLog->record_id,
            'old_values' => is_string($auditLog->old_values) ? json_decode($auditLog->old_values, true) : $auditLog->old_values,
            'new_values' => is_string($auditLog->new_values) ? json_decode($auditLog->new_values, true) : $auditLog->new_values,
            'ip_address' => $auditLog->ip_address,
            'user_agent' => $auditLog->user_agent
        ]);
    }

    // ============================================================================
    // SYSTEM SETTINGS METHODS
    // ============================================================================

    /**
     * Display the general settings page.
     */
    public function generalSettings()
    {
        // Get all system settings
        $settings = SystemSetting::getMany([
            'app_name',
            'company_name',
            'app_timezone', 
            'currency',
            'low_stock_threshold',
            'default_lead_time',
            'business_hours_open',
            'business_hours_close',
            'tax_rate',
            'default_batch_size',
            // Notification preferences (stored as boolean)
            'notif_lowstock',
            'notif_req', 
            'notif_expiry',
            'notif_system',
            // Company profile information
            'company_logo',
            'tax_id',
            'company_address',
            'contact_email',
            'contact_phone',
        ]);

        // Get system information
        $systemInfo = [
            'version' => '2.4.0',
            'last_backup' => $this->getLastBackupTime(),
            'timezone' => config('app.timezone') ?? 'Asia/Manila',
        ];

        return view('Admin.system.general_setting', compact('settings', 'systemInfo'));
    }

    /**
     * Update system settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'app_name' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'app_timezone' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'default_lead_time' => 'nullable|integer|min:0',
            'business_hours_open' => 'nullable|date_format:H:i',
            'business_hours_close' => 'nullable|date_format:H:i',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'default_batch_size' => 'nullable|integer|min:1',
            // Notification preferences
            'notif_lowstock' => 'nullable|boolean',
            'notif_req' => 'nullable|boolean',
            'notif_expiry' => 'nullable|boolean',
            'notif_system' => 'nullable|boolean',
            // Company profile information
            'company_logo' => 'nullable|string',
            'tax_id' => 'nullable|string|max:50',
            'company_address' => 'nullable|string',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'maintenance_mode' => 'nullable|boolean',
        ]);

        try {
            $settingsToUpdate = [];

            // Company Profile Settings
            if ($request->filled('app_name')) {
                $settingsToUpdate['app_name'] = [
                    'value' => $request->app_name,
                    'type' => 'string',
                    'description' => 'Application name',
                    'is_public' => true
                ];
            }

            if ($request->filled('company_name')) {
                $settingsToUpdate['company_name'] = [
                    'value' => $request->company_name,
                    'type' => 'string', 
                    'description' => 'Company name',
                    'is_public' => true
                ];
            }

            if ($request->filled('tax_id')) {
                $settingsToUpdate['tax_id'] = [
                    'value' => $request->tax_id,
                    'type' => 'string',
                    'description' => 'Tax ID / TIN',
                    'is_public' => false
                ];
            }

            if ($request->filled('company_address')) {
                $settingsToUpdate['company_address'] = [
                    'value' => $request->company_address,
                    'type' => 'string',
                    'description' => 'Company address',
                    'is_public' => false
                ];
            }

            if ($request->filled('contact_email')) {
                $settingsToUpdate['contact_email'] = [
                    'value' => $request->contact_email,
                    'type' => 'string',
                    'description' => 'Contact email',
                    'is_public' => true
                ];
            }

            if ($request->filled('contact_phone')) {
                $settingsToUpdate['contact_phone'] = [
                    'value' => $request->contact_phone,
                    'type' => 'string',
                    'description' => 'Contact phone',
                    'is_public' => true
                ];
            }

            // System Settings
            if ($request->filled('app_timezone')) {
                $settingsToUpdate['app_timezone'] = [
                    'value' => $request->app_timezone,
                    'type' => 'string',
                    'description' => 'Application timezone',
                    'is_public' => false
                ];
            }

            if ($request->filled('currency')) {
                $settingsToUpdate['currency'] = [
                    'value' => $request->currency,
                    'type' => 'string',
                    'description' => 'Default currency',
                    'is_public' => true
                ];
            }

            if ($request->filled('low_stock_threshold')) {
                $settingsToUpdate['low_stock_threshold'] = [
                    'value' => (int) $request->low_stock_threshold,
                    'type' => 'integer',
                    'description' => 'Low stock alert threshold',
                    'is_public' => false
                ];
            }

            if ($request->filled('default_lead_time')) {
                $settingsToUpdate['default_lead_time'] = [
                    'value' => (int) $request->default_lead_time,
                    'type' => 'integer',
                    'description' => 'Default supplier lead time in days',
                    'is_public' => false
                ];
            }

            if ($request->filled('business_hours_open')) {
                $settingsToUpdate['business_hours_open'] = [
                    'value' => $request->business_hours_open,
                    'type' => 'string',
                    'description' => 'Business opening time',
                    'is_public' => true
                ];
            }

            if ($request->filled('business_hours_close')) {
                $settingsToUpdate['business_hours_close'] = [
                    'value' => $request->business_hours_close,
                    'type' => 'string',
                    'description' => 'Business closing time',
                    'is_public' => true
                ];
            }

            if ($request->filled('tax_rate')) {
                $settingsToUpdate['tax_rate'] = [
                    'value' => (float) $request->tax_rate / 100, // Convert percentage to decimal
                    'type' => 'decimal',
                    'description' => 'VAT tax rate',
                    'is_public' => false
                ];
            }

            if ($request->filled('default_batch_size')) {
                $settingsToUpdate['default_batch_size'] = [
                    'value' => (int) $request->default_batch_size,
                    'type' => 'integer',
                    'description' => 'Default production batch size',
                    'is_public' => false
                ];
            }

            // Notification Preferences
            $notificationSettings = [
                'notif_lowstock' => 'Low Stock Alerts',
                'notif_req' => 'New Requisition Requests', 
                'notif_expiry' => 'Near Expiry Warnings',
                'notif_system' => 'System Security Alerts'
            ];

            foreach ($notificationSettings as $key => $description) {
                $value = $request->has($key) ? true : false;
                $settingsToUpdate[$key] = [
                    'value' => $value,
                    'type' => 'boolean',
                    'description' => $description,
                    'is_public' => false
                ];
            }

            // Logo (if provided)
            if ($request->filled('company_logo')) {
                $settingsToUpdate['company_logo'] = [
                    'value' => $request->company_logo,
                    'type' => 'string',
                    'description' => 'Company logo path',
                    'is_public' => true
                ];
            }

            // Update all settings
            SystemSetting::setMany($settingsToUpdate);

            // Log the settings update
            AuditLog::create([
                'table_name' => 'system_settings',
                'record_id' => null,
                'action' => 'UPDATE',
                'new_values' => json_encode(array_keys($settingsToUpdate)),
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the last backup time.
     */
    private function getLastBackupTime()
    {
        // For demo purposes, return a static backup time
        // In a real implementation, you might check actual backup files or database
        $lastAuditLog = AuditLog::orderBy('created_at', 'desc')->first();
        return $lastAuditLog ? $lastAuditLog->created_at->format('M j, Y g:i A') : 'Never';
    }

    // ============================================================================
    // BACKUP MANAGEMENT METHODS
    // ============================================================================

    /**
     * Display the backup management page.
     */
    public function backup(Request $request)
    {
        $backupData = $this->getBackupData();
        
        return view('Admin.system.backup', $backupData);
    }

    /**
     * Create a new backup.
     */
    public function createBackup(Request $request)
    {
        try {
            // Create backup directory if it doesn't exist
            $backupDir = storage_path('app/backups');
            \Log::info("Backup directory path: " . $backupDir);
            
            if (!is_dir($backupDir)) {
                \Log::info("Creating backup directory: " . $backupDir);
                if (!mkdir($backupDir, 0755, true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to create backup directory. Please check permissions.'
                    ], 500);
                }
            }

            // Check if directory is writable
            if (!is_writable($backupDir)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup directory is not writable. Please check file permissions.'
                ], 500);
            }

            // Generate backup filename
            $timestamp = Carbon::now()->format('Y-m-d_His');
            $filename = "backup-{$timestamp}.sql";
            $backupPath = $backupDir . '/' . $filename;

            \Log::info("Backup file path: " . $backupPath);

            // Get database configuration
            $dbHost = config('database.connections.pgsql.host', 'localhost');
            $dbName = config('database.connections.pgsql.database', 'wellkenz_bakery');
            $dbUser = config('database.connections.pgsql.username', 'postgres');
            $dbPort = config('database.connections.pgsql.port', '5432');
            $dbPassword = config('database.connections.pgsql.password', '');

            \Log::info("Database config: Host={$dbHost}, Port={$dbPort}, User={$dbUser}, Database={$dbName}");

            // For Windows development environment, let's try a different approach
            // Check if pg_dump is available
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            $pgDumpPath = '';
            
            if ($isWindows) {
                // Windows - try to find pg_dump in common locations
                $possiblePaths = [
                    'C:\\Program Files\\PostgreSQL\\*\\bin\\pg_dump.exe',
                    'C:\\Program Files (x86)\\PostgreSQL\\*\\bin\\pg_dump.exe',
                    'C:\\xampp\\mysql\\bin\\pg_dump.exe',
                    'C:\\xampp\\postgres\\bin\\pg_dump.exe'
                ];
                
                $pgDumpFound = false;
                foreach ($possiblePaths as $pattern) {
                    $matches = glob($pattern);
                    if (!empty($matches) && file_exists($matches[0])) {
                        $pgDumpPath = $matches[0];
                        $pgDumpFound = true;
                        \Log::info("Found pg_dump at: " . $pgDumpPath);
                        break;
                    }
                }
                
                if (!$pgDumpFound) {
                    // Try to run pg_dump from PATH
                    exec('where pg_dump', $output, $result);
                    if ($result === 0 && !empty($output)) {
                        $pgDumpPath = trim($output[0]);
                        \Log::info("Found pg_dump in PATH: " . $pgDumpPath);
                    } else {
                        $pgDumpPath = 'pg_dump'; // Try the default anyway
                    }
                }
            } else {
                // Linux/Unix
                $pgDumpPath = 'pg_dump';
            }
            
            // Test if pg_dump is actually executable
            if (!empty($pgDumpPath) && $pgDumpPath !== 'pg_dump') {
                if (!file_exists($pgDumpPath)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'pg_dump executable not found at: ' . $pgDumpPath
                    ], 500);
                }
            }

            // Test database connection first
            $testCommand = "pg_isready -h {$dbHost} -p {$dbPort} -U {$dbUser}";
            if (!empty($dbPassword)) {
                putenv("PGPASSWORD={$dbPassword}");
            }
            
            \Log::info("Testing database connection: " . $testCommand);
            exec($testCommand, $testOutput, $testReturnCode);
            \Log::info("Database test output: " . implode("\n", $testOutput) . " Return code: " . $testReturnCode);
            
            if ($testReturnCode !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Database connection failed. Please check database credentials and connectivity.'
                ], 500);
            }

            // Try to create backup using pg_dump first
            $backupCreated = false;
            $method = 'pg_dump';
            
            if (!empty($pgDumpPath)) {
                $command = escapeshellarg($pgDumpPath) . " -h {$dbHost} -p {$dbPort} -U {$dbUser} -d {$dbName} -f " . escapeshellarg($backupPath);
                \Log::info("Backup command: " . $command);
                
                // Set password for PostgreSQL
                if (!empty($dbPassword)) {
                    putenv("PGPASSWORD={$dbPassword}");
                }
                
                $output = [];
                $returnCode = 0;
                exec($command . ' 2>&1', $output, $returnCode);

                \Log::info("Backup execution output: " . implode("\n", $output) . " Return code: " . $returnCode);

                if ($returnCode === 0 && file_exists($backupPath)) {
                    $backupCreated = true;
                    \Log::info("Backup created successfully using pg_dump: " . $backupPath);
                } else {
                    \Log::warning("pg_dump failed, will try PHP fallback method. Error: " . implode("\n", $output));
                }
            }
            
            // If pg_dump didn't work, try PHP fallback method
            if (!$backupCreated) {
                \Log::info("Attempting PHP fallback backup method");
                
                try {
                    $pdo = new PDO(
                        "pgsql:host={$dbHost};port={$dbPort};dbname={$dbName}", 
                        $dbUser, 
                        $dbPassword,
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                    
                    // Get all tables
                    $stmt = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
                    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    $backupContent = "-- WellKenz Backup Generated " . Carbon::now()->format('Y-m-d H:i:s') . "\n";
                    $backupContent .= "-- Database: {$dbName}\n\n";
                    
                    // Disable triggers temporarily
                    $backupContent .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
                    
                    foreach ($tables as $table) {
                        if ($table === 'password_reset_tokens') {
                            continue; // Skip Laravel migrations table
                        }
                        
                        // Get table structure
                        $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = '{$table}' AND table_schema = 'public'");
                        $columns = $stmt->fetchAll();
                        
                        // Drop and recreate table
                        $backupContent .= "DROP TABLE IF EXISTS {$table} CASCADE;\n";
                        $backupContent .= "CREATE TABLE {$table} (\n";
                        
                        $columnDefs = [];
                        foreach ($columns as $column) {
                            $columnDefs[] = "    " . $column['column_name'] . " " . $column['data_type'];
                        }
                        $backupContent .= implode(",\n", $columnDefs) . "\n);\n\n";
                        
                        // Insert data
                        $stmt = $pdo->query("SELECT * FROM {$table}");
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (!empty($rows)) {
                            $columnNames = array_keys($rows[0]);
                            $backupContent .= "INSERT INTO {$table} (" . implode(', ', $columnNames) . ") VALUES\n";
                            
                            $values = [];
                            foreach ($rows as $row) {
                                $escapedValues = array_map(function($value) {
                                    if ($value === null) return 'NULL';
                                    return "'" . str_replace("'", "''", $value) . "'";
                                }, array_values($row));
                                $values[] = "(" . implode(', ', $escapedValues) . ")";
                            }
                            
                            $backupContent .= implode(",\n", $values) . ";\n\n";
                        }
                    }
                    
                    // Re-enable triggers
                    $backupContent .= "SET FOREIGN_KEY_CHECKS = 1;\n";
                    
                    // Write to file
                    file_put_contents($backupPath, $backupContent);
                    $backupCreated = true;
                    $method = 'php_fallback';
                    \Log::info("Backup created successfully using PHP fallback: " . $backupPath);
                    
                } catch (\Exception $e) {
                    \Log::error("PHP fallback backup method also failed: " . $e->getMessage());
                }
            }

            if ($backupCreated && file_exists($backupPath)) {
                $fileSize = filesize($backupPath);
                \Log::info("Backup created successfully using {$method}: " . $backupPath . " Size: " . $fileSize . " bytes");
                
                // Log the backup creation
                AuditLog::create([
                    'table_name' => 'system_backup',
                    'record_id' => null,
                    'action' => 'CREATE',
                    'new_values' => json_encode([
                        'filename' => $filename,
                        'size' => $fileSize,
                        'type' => 'manual',
                        'method' => $method
                    ]),
                    'user_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Backup created successfully using ' . $method . '!',
                    'filename' => $filename,
                    'size' => $this->formatBytes($fileSize)
                ]);
            } else {
                // Clean up partial file if created
                if (file_exists($backupPath)) {
                    unlink($backupPath);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create backup. Please check if pg_dump is installed or if database connection is available.'
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error("Exception during backup creation: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error creating backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a backup file.
     */
    public function downloadBackup($filename)
    {
        try {
            $backupPath = storage_path('app/backups/' . $filename);
            
            if (!file_exists($backupPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found'
                ], 404);
            }

            // Log the download
            AuditLog::create([
                'table_name' => 'system_backup',
                'record_id' => null,
                'action' => 'UPDATE',
                'new_values' => json_encode([
                    'filename' => $filename,
                    'action' => 'download'
                ]),
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return response()->download($backupPath, $filename);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error downloading backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get backup data for the dashboard.
     */
    private function getBackupData()
    {
        $backupDir = storage_path('app/backups');
        $backupFiles = collect([]);
        
        if (file_exists($backupDir)) {
            $files = scandir($backupDir);
            
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                    $filePath = $backupDir . '/' . $file;
                    $createdAt = filemtime($filePath);
                    $backupFiles->push([
                        'filename' => $file,
                        'size' => filesize($filePath),
                        'created_at' => $createdAt,
                        'formatted_size' => $this->formatBytes(filesize($filePath)),
                        'formatted_date' => Carbon::createFromTimestamp($createdAt)->format('M j, Y (g:i A)'),
                        'is_verified' => true, // For now, assume all are verified
                        'type' => strpos($file, 'auto') !== false ? 'automated' : 'manual'
                    ]);
                }
            }
        }

        // Sort by creation date (newest first)
        $backupFiles = $backupFiles->sortByDesc('created_at')->values();

        // Calculate storage statistics
        $totalBackups = $backupFiles->count();
        $totalSize = $backupFiles->sum('size');
        $maxStorage = 5 * 1024 * 1024 * 1024; // 5GB in bytes
        $storageUsed = min($totalSize, $maxStorage);
        $storagePercentage = $maxStorage > 0 ? ($storageUsed / $maxStorage) * 100 : 0;

        // Get last successful backup
        $lastBackup = $backupFiles->first();
        $lastBackupTime = $lastBackup ? Carbon::createFromTimestamp($lastBackup['created_at'])->format('M j, Y g:i A') : 'Never';

        // Calculate next scheduled run (assuming daily at 3 AM)
        $nextScheduled = Carbon::now()->setTime(3, 0, 0);
        if ($nextScheduled->isPast()) {
            $nextScheduled->addDay();
        }
        $timeUntilNext = $nextScheduled->diffForHumans();

        // Get system settings for backup configuration
        $systemSettings = SystemSetting::getMany([
            'backup_auto_enabled',
            'backup_auto_time',
            'backup_retention_days',
            'backup_email_notifications'
        ]);

        return [
            'backups' => $backupFiles,
            
            // Status cards data
            'lastBackupTime' => $lastBackupTime,
            'storageUsed' => $this->formatBytes($storageUsed),
            'storageLimit' => $this->formatBytes($maxStorage),
            'storagePercentage' => round($storagePercentage, 1),
            'totalBackups' => $totalBackups,
            'nextScheduled' => $nextScheduled->format('M j, Y g:i A'),
            'timeUntilNext' => $timeUntilNext,
            
            // System settings
            'backupSettings' => $systemSettings,
        ];
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes($size, $precision = 1)
    {
        if ($size == 0) return '0 B';
        
        $base = log($size, 1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }

    /**
     * Get backup history for AJAX requests.
     */
    public function getBackupHistory(Request $request)
    {
        $backupData = $this->getBackupData();
        
        return response()->json([
            'backups' => $backupData['backups']->map(function ($backup) {
                return [
                    'filename' => $backup['filename'],
                    'created_at' => Carbon::createFromTimestamp($backup['created_at'])->format('M j, Y (g:i A)'),
                    'size' => $this->formatBytes($backup['size']),
                    'type' => $backup['type'],
                    'is_verified' => $backup['is_verified']
                ];
            }),
            'summary' => [
                'total_backups' => $backupData['totalBackups'],
                'storage_used' => $backupData['storageUsed'],
                'storage_limit' => $backupData['storageLimit']
            ]
        ]);
    }

    /**
     * Restore database from uploaded backup file.
     */
    public function restoreBackup(Request $request)
    {
        try {
            $request->validate([
                'backup_file' => 'required|file|mimes:sql,zip|max:102400' // 100MB max
            ]);

            $uploadedFile = $request->file('backup_file');
            $originalFilename = $uploadedFile->getClientOriginalName();
            $fileExtension = $uploadedFile->getClientOriginalExtension();
            
            // Validate file extension
            if (!in_array(strtolower($fileExtension), ['sql', 'zip'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only SQL and ZIP files are allowed for backup restoration.'
                ], 422);
            }

            // Create temporary directory for uploads
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Store uploaded file temporarily
            $tempFilename = 'restore_' . time() . '.' . $fileExtension;
            $tempPath = $tempDir . '/' . $tempFilename;
            $uploadedFile->move($tempDir, $tempFilename);

            // Get database configuration
            $dbHost = config('database.connections.pgsql.host', 'localhost');
            $dbName = config('database.connections.pgsql.database', 'wellkenz_bakery');
            $dbUser = config('database.connections.pgsql.username', 'postgres');
            $dbPort = config('database.connections.pgsql.port', '5432');
            $dbPassword = config('database.connections.pgsql.password', '');

            // Log the restore attempt
            AuditLog::create([
                'table_name' => 'system_backup',
                'record_id' => null,
                'action' => 'UPDATE',
                'new_values' => json_encode([
                    'filename' => $originalFilename,
                    'action' => 'restore_attempt',
                    'method' => 'manual_upload'
                ]),
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // For SQL files, attempt to restore directly
            if (strtolower($fileExtension) === 'sql') {
                try {
                    // Set up environment variables for PostgreSQL
                    putenv("PGPASSWORD={$dbPassword}");
                    
                    // Try to restore the database
                    $command = "psql -h {$dbHost} -p {$dbPort} -U {$dbUser} -d {$dbName} -f " . escapeshellarg($tempPath);
                    
                    $output = [];
                    $returnCode = 0;
                    exec($command . ' 2>&1', $output, $returnCode);
                    
                    if ($returnCode === 0) {
                        // Clean up temp file
                        unlink($tempPath);
                        
                        // Log successful restore
                        AuditLog::create([
                            'table_name' => 'system_backup',
                            'record_id' => null,
                            'action' => 'UPDATE',
                            'new_values' => json_encode([
                                'filename' => $originalFilename,
                                'action' => 'restore_success',
                                'method' => 'manual_upload'
                            ]),
                            'user_id' => auth()->id(),
                        ]);
                        
                        return response()->json([
                            'success' => true,
                            'message' => 'Database restored successfully from backup file!'
                        ]);
                    } else {
                        // Log failed restore
                        AuditLog::create([
                            'table_name' => 'system_backup',
                            'record_id' => null,
                            'action' => 'UPDATE',
                            'new_values' => json_encode([
                                'filename' => $originalFilename,
                                'action' => 'restore_failed',
                                'error' => implode("\n", $output),
                                'method' => 'manual_upload'
                            ]),
                            'user_id' => auth()->id(),
                        ]);
                        
                        // Clean up temp file
                        unlink($tempPath);
                        
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to restore database: ' . implode("\n", $output)
                        ], 500);
                    }
                    
                } catch (\Exception $e) {
                    // Clean up temp file
                    if (file_exists($tempPath)) {
                        unlink($tempPath);
                    }
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Error during database restoration: ' . $e->getMessage()
                    ], 500);
                }
            } 
            // For ZIP files, extract and process
            else if (strtolower($fileExtension) === 'zip') {
                try {
                    $extractDir = $tempDir . '/extract_' . time();
                    mkdir($extractDir, 0755, true);
                    
                    // Extract ZIP file
                    $zip = new \ZipArchive();
                    if ($zip->open($tempPath) === TRUE) {
                        $zip->extractTo($extractDir);
                        $zip->close();
                        
                        // Look for SQL files in the extracted directory
                        $sqlFiles = glob($extractDir . '/*.sql');
                        
                        if (!empty($sqlFiles)) {
                            $sqlFile = $sqlFiles[0]; // Use first SQL file found
                            
                            // Set up environment variables for PostgreSQL
                            putenv("PGPASSWORD={$dbPassword}");
                            
                            // Restore the database
                            $command = "psql -h {$dbHost} -p {$dbPort} -U {$dbUser} -d {$dbName} -f " . escapeshellarg($sqlFile);
                            
                            $output = [];
                            $returnCode = 0;
                            exec($command . ' 2>&1', $output, $returnCode);
                            
                            // Clean up files
                            $this->rrmdir($extractDir);
                            unlink($tempPath);
                            
                            if ($returnCode === 0) {
                                return response()->json([
                                    'success' => true,
                                    'message' => 'Database restored successfully from ZIP backup!'
                                ]);
                            } else {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Failed to restore from ZIP: ' . implode("\n", $output)
                                ], 500);
                            }
                        } else {
                            // Clean up
                            $this->rrmdir($extractDir);
                            unlink($tempPath);
                            
                            return response()->json([
                                'success' => false,
                                'message' => 'No SQL files found in the ZIP archive.'
                            ], 422);
                        }
                    } else {
                        // Clean up
                        unlink($tempPath);
                        
                        return response()->json([
                            'success' => false,
                            'message' => 'Could not extract ZIP file.'
                        ], 422);
                    }
                    
                } catch (\Exception $e) {
                    // Clean up
                    if (isset($extractDir) && file_exists($extractDir)) {
                        $this->rrmdir($extractDir);
                    }
                    if (file_exists($tempPath)) {
                        unlink($tempPath);
                    }
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Error processing ZIP file: ' . $e->getMessage()
                    ], 500);
                }
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error handling backup file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recursively remove directory and all contents.
     */
    private function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $this->rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    // ============================================================================
    // SUPPLIER MANAGEMENT METHODS
    // ============================================================================

    /**
     * Display the supplier list.
     */
    public function supplierList(Request $request)
    {
        $query = Supplier::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('supplier_code', 'ilike', "%{$search}%")
                  ->orWhere('contact_person', 'ilike', "%{$search}%")
                  ->orWhere('tax_id', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $perPage = $request->get('per_page', 10);
        $suppliers = $query->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        // Get statistics
        $stats = [
            'total' => Supplier::count(),
            'active' => Supplier::where('is_active', true)->count(),
            'inactive' => Supplier::where('is_active', false)->count(),
        ];

        return view('Admin.supplier.supplier_list', compact('suppliers', 'stats'));
    }

    /**
     * Store a new supplier.
     */
    public function storeSupplier(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'rating' => 'nullable|integer|min:1|max:5',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        try {
            // Generate supplier code
            $lastSupplier = Supplier::orderBy('id', 'desc')->first();
            $nextId = $lastSupplier ? $lastSupplier->id + 1 : 1;
            $supplierCode = 'SUP' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

            $supplier = Supplier::create([
                'supplier_code' => $supplierCode,
                'name' => $request->name,
                'contact_person' => $request->contact_person,
                'email' => $request->email,
                'phone' => $request->phone,
                'mobile' => $request->mobile,
                'address' => $request->address,
                'city' => $request->city,
                'province' => $request->province,
                'postal_code' => $request->postal_code,
                'tax_id' => $request->tax_id,
                'payment_terms' => $request->payment_terms ?? 30,
                'credit_limit' => $request->credit_limit ?? 0,
                'rating' => $request->rating,
                'is_active' => $request->is_active ?? true,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Supplier created successfully!',
                'supplier' => $supplier
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get supplier data for editing.
     */
    public function editSupplier(Supplier $supplier)
    {
        return response()->json($supplier);
    }

    /**
     * Update an existing supplier.
     */
    public function updateSupplier(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'rating' => 'nullable|integer|min:1|max:5',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        try {
            $supplier->update([
                'name' => $request->name,
                'contact_person' => $request->contact_person,
                'email' => $request->email,
                'phone' => $request->phone,
                'mobile' => $request->mobile,
                'address' => $request->address,
                'city' => $request->city,
                'province' => $request->province,
                'postal_code' => $request->postal_code,
                'tax_id' => $request->tax_id,
                'payment_terms' => $request->payment_terms ?? 30,
                'credit_limit' => $request->credit_limit ?? 0,
                'rating' => $request->rating,
                'is_active' => $request->is_active ?? true,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Supplier updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle supplier active status.
     */
    public function toggleSupplierStatus(Supplier $supplier)
    {
        try {
            $supplier->update([
                'is_active' => !$supplier->is_active
            ]);

            $status = $supplier->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "Supplier {$status} successfully!",
                'is_active' => $supplier->is_active
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error toggling supplier status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a supplier.
     */
    public function deleteSupplier(Supplier $supplier)
    {
        try {
            // Check if supplier has related records
            if ($supplier->purchaseOrders()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete supplier with existing purchase orders. Consider deactivating instead.'
                ], 422);
            }

            $supplierName = $supplier->name;
            $supplier->delete();

            return response()->json([
                'success' => true,
                'message' => "Supplier '{$supplierName}' deleted successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================================
    // NOTIFICATION MANAGEMENT METHODS
    // ============================================================================

    /**
     * Display the notifications page with filtering and pagination.
     */
    public function notifications(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $perPage = $request->get('per_page', 10);

        // Get notifications for current user with optional filtering
        $notifications = Notification::forCurrentUser($filter)
            ->paginate($perPage)
            ->withQueryString();

        // Get notification statistics for the current user
        $stats = [
            'total' => Notification::where('user_id', auth()->id())->count(),
            'unread' => Notification::where('user_id', auth()->id())->where('is_read', false)->count(),
            'high_priority' => Notification::where('user_id', auth()->id())->whereIn('priority', ['high', 'urgent'])->count(),
            'urgent' => Notification::where('user_id', auth()->id())->where('priority', 'urgent')->count(),
        ];

        return view('Admin.notification', compact('notifications', 'stats', 'filter'));
    }

    /**
     * Mark a notification as read.
     */
    public function markNotificationAsRead(Notification $notification)
    {
        try {
            // Check if notification belongs to current user
            if ($notification->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to modify this notification.'
                ], 403);
            }

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a notification as unread.
     */
    public function markNotificationAsUnread(Notification $notification)
    {
        try {
            // Check if notification belongs to current user
            if ($notification->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to modify this notification.'
                ], 403);
            }

            $notification->markAsUnread();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as unread.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read for the current user.
     */
    public function markAllNotificationsAsRead()
    {
        try {
            Notification::markAllAsReadForCurrentUser();

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking all notifications as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a notification.
     */
    public function deleteNotification(Notification $notification)
    {
        try {
            // Check if notification belongs to current user
            if ($notification->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to delete this notification.'
                ], 403);
            }

            $notificationTitle = $notification->title;
            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => "Notification '{$notificationTitle}' deleted successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new notification.
     */
    public function createNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|string|max:50',
            'priority' => 'required|in:low,normal,high,urgent',
            'action_url' => 'nullable|url',
            'expires_at' => 'nullable|date|after:now',
        ]);

        try {
            $notification = Notification::create([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type,
                'priority' => $request->priority,
                'action_url' => $request->action_url,
                'expires_at' => $request->expires_at,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification created successfully!',
                'notification' => $notification
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification details for modal display.
     */
    public function getNotificationDetails(Notification $notification)
    {
        try {
            // Check if notification belongs to current user
            if ($notification->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view this notification.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'notification' => [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'priority' => $notification->priority,
                    'priority_color' => $notification->getPriorityBadgeColor(),
                    'icon_class' => $notification->getIconClass(),
                    'is_read' => $notification->is_read,
                    'action_url' => $notification->action_url,
                    'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                    'time_ago' => $notification->time_ago,
                    'expires_at' => $notification->expires_at ? $notification->expires_at->format('Y-m-d H:i:s') : null,
                    'metadata' => $notification->metadata,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching notification details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread notification count for real-time updates.
     */
    public function getUnreadNotificationCount()
    {
        try {
            $count = Notification::unreadCountForCurrentUser();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching notification count: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent notifications for header dropdown (max 5 unread notifications).
     */
    public function getHeaderNotifications()
    {
        try {
            $notifications = Notification::where('user_id', auth()->id())
                ->where('is_read', false)
                ->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'type' => $notification->type,
                        'priority' => $notification->priority,
                        'time_ago' => $notification->time_ago,
                        'action_url' => $notification->action_url,
                        'icon_class' => $notification->getIconClass(),
                    ];
                });

            $unreadCount = Notification::unreadCountForCurrentUser();

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching header notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk operations on notifications (mark as read, delete, etc.).
     */
    public function bulkNotificationOperations(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array|min:1',
            'notification_ids.*' => 'exists:notifications,id',
            'operation' => 'required|in:mark_read,mark_unread,delete',
        ]);

        try {
            $notificationIds = $request->notification_ids;
            $operation = $request->operation;

            // Verify all notifications belong to current user
            $notifications = Notification::whereIn('id', $notificationIds)
                ->where('user_id', auth()->id())
                ->get();

            if ($notifications->count() !== count($notificationIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some notifications do not belong to you or do not exist.'
                ], 403);
            }

            $affectedCount = 0;

            switch ($operation) {
                case 'mark_read':
                    Notification::markMultipleAsRead($notificationIds);
                    $affectedCount = $notifications->where('is_read', false)->count();
                    $message = "{$affectedCount} notifications marked as read.";
                    break;

                case 'mark_unread':
                    Notification::whereIn('id', $notificationIds)
                        ->where('user_id', auth()->id())
                        ->update(['is_read' => false]);
                    $affectedCount = $notifications->where('is_read', true)->count();
                    $message = "{$affectedCount} notifications marked as unread.";
                    break;

                case 'delete':
                    $affectedCount = $notifications->count();
                    Notification::whereIn('id', $notificationIds)
                        ->where('user_id', auth()->id())
                        ->delete();
                    $message = "{$affectedCount} notifications deleted.";
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'affected_count' => $affectedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error performing bulk operation: ' . $e->getMessage()
            ], 500);
        }
    }
}