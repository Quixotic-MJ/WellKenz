<?php

namespace App\Http\Controllers\Admin\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    /**
     * Display user roles and permissions.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
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
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
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
            Log::info('New role created:', $newRole);

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
     * Get role details with users for modal display.
     *
     * @param string $role
     * @return \Illuminate\Http\Response
     */
    public function details($role)
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
     *
     * @param \Illuminate\Http\Request $request
     * @param string $role
     * @return \Illuminate\Http\Response
     */
    public function savePermissions(Request $request, $role)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string'
        ]);

        try {
            // In a real application, you would save this to a permissions table
            // For now, we'll just log it
            Log::info("Updated permissions for role {$role}:", $request->permissions);

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
     * Create a new role with alias method.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function createRole(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Get role details with alias method.
     *
     * @param string $role
     * @return \Illuminate\Http\Response
     */
    public function getRoleDetails($role)
    {
        return $this->details($role);
    }

    /**
     * Get current role permissions.
     *
     * @param string $role
     * @return \Illuminate\Http\Response
     */
    public function getPermissions($role)
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
     * Get formatted role name.
     *
     * @param string $role
     * @return string
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
     *
     * @param string $role
     * @return string
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
     *
     * @param string $role
     * @return string
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
     *
     * @param string $role
     * @return string
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
     *
     * @param string $role
     * @return string
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