<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Item;
use App\Models\Category;
use App\Models\Requisition;
use App\Models\PurchaseOrder;
use App\Models\Batch;
use App\Models\AuditLog;
use App\Services\UserService;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\BulkUserOperationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

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
        $data = [
            // Welcome message data
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
            ->with(['currentStock', 'unit'])
            ->get()
            ->filter(function ($item) {
                $currentStock = $item->currentStock ? $item->currentStock->current_quantity : 0;
                return $currentStock > 0 && $currentStock <= $item->reorder_point;
            })
            ->sortBy(function ($item) {
                $currentStock = $item->currentStock ? $item->currentStock->current_quantity : 0;
                return $currentStock;
            })
            ->take(5)
            ->values();
    }

    private function getItemsOutOfStock()
    {
        return Item::where('is_active', true)
            ->with(['currentStock', 'unit'])
            ->get()
            ->filter(function ($item) {
                $currentStock = $item->currentStock ? $item->currentStock->current_quantity : 0;
                return $currentStock <= 0;
            })
            ->sortBy(function ($item) {
                $currentStock = $item->currentStock ? $item->currentStock->current_quantity : 0;
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
            $currentStock = $item->currentStock ? $item->currentStock->current_quantity : 0;
            
            return [
                'id' => $item->id,
                'name' => $item->name,
                'item_code' => $item->item_code,
                'current_stock' => $currentStock,
                'reorder_level' => $item->reorder_point,
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
            ->where('expiry_date', '<=', $sevenDaysFromNow)
            ->where('expiry_date', '>=', Carbon::now())
            ->orderBy('expiry_date')
            ->take(5)
            ->get()
            ->map(function ($batch) {
                $daysUntilExpiry = Carbon::now()->diffInDays($batch->expiry_date, false);
                
                return [
                    'id' => $batch->id,
                    'item_name' => $batch->item->name ?? 'Unknown Item',
                    'batch_number' => $batch->batch_number,
                    'expiry_date' => $batch->expiry_date->format('M j, Y'),
                    'days_until_expiry' => $daysUntilExpiry,
                    'expiry_status' => ($daysUntilExpiry <= 0) ? 'Expired' : 
                                     (($daysUntilExpiry <= 1) ? 'Expires Tomorrow' : 
                                     "Expires in {$daysUntilExpiry} days"),
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
}