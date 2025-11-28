<?php

namespace App\Http\Controllers\Admin\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\BulkUserOperationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
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
        $this->middleware('auth');
    }

    /**
     * Display a listing of users.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
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

        // Get current authenticated admin ID to exclude from bulk operations
        $currentAdminId = auth()->id();

        return view('Admin.user_management.all_user', compact('users', 'roleStats', 'currentAdminId'));
    }

    /**
     * Store a newly created user in storage.
     *
     * @param StoreUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
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
     * Display the specified user.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $user->load('profile');
        return view('Admin.user_management.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $userData = $this->userService->getUserForEdit($user);
        return response()->json($userData);
    }

    /**
     * Update the specified user in storage.
     *
     * @param UpdateUserRequest $request
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, User $user)
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
     * Remove the specified user from storage.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
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
     * Toggle user active/inactive status.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function toggleStatus(User $user)
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
     * Reset user password.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(User $user)
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
     * Change user password.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User|null $user
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request, ?User $user = null)
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
     *
     * @param BulkUserOperationRequest $request
     * @return \Illuminate\Http\Response
     */
    public function bulkOperations(BulkUserOperationRequest $request)
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
     * Get user data for editing.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function getUserData()
    {
        // Predefined department options (excluding Sales as requested)
        $departments = [
            'Administration',
            'Production', 
            'Purchasing',
            'Inventory'
        ];

        // Predefined position options
        $positions = [
            'Manager',
            'Supervisor',
            'Officer', 
            'Staff',
            'Assistant'
        ];

        return response()->json([
            'departments' => $departments,
            'positions' => $positions
        ]);
    }

    /**
     * Search users for AJAX requests.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $users = $this->userService->searchUsers($query);
        return response()->json($users);
    }

    /**
     * Filter users via AJAX for smoother search experience.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function filterUsers(Request $request)
    {
        try {
            $filters = [
                'search' => $request->get('search'),
                'role' => $request->get('role'),
                'status' => $request->get('status')
            ];

            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            // Get paginated users with filters
            $users = $this->userService->getPaginatedUsers($filters, $perPage);

            // Format users for JSON response
            $formattedUsers = $users->map(function ($user) {
                // Handle last login formatting safely
                $lastLoginFormatted = 'Never';
                if ($user->last_login_at) {
                    $lastLoginFormatted = $user->last_login_at->diffForHumans();
                }
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'formatted_role' => $user->formatted_role,
                    'role_color_class' => $user->role_color_class,
                    'is_active' => $user->is_active,
                    'formatted_last_login' => $lastLoginFormatted,
                    'initials' => $user->initials,
                    'employee_id' => $user->profile?->employee_id,
                    'profile_photo_path' => $user->profile?->profile_photo_path,
                    'current_admin_id' => auth()->id(),
                    'is_current_admin' => $user->id === auth()->id()
                ];
            });

            return response()->json([
                'success' => true,
                'users' => $formattedUsers,
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error filtering users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export users data to CSV format.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportUsers(Request $request)
    {
        try {
            $filters = [
                'search' => $request->get('search'),
                'role' => $request->get('role'),
                'status' => $request->get('status')
            ];

            // Get all users with applied filters (no pagination for export)
            $users = $this->userService->getPaginatedUsers($filters, 100000); // Large number to get all results
            
            // Prepare CSV data
            $csvData = [];
            $csvData[] = [
                'ID',
                'Employee ID', 
                'Full Name',
                'Email Address',
                'Role',
                'Department',
                'Position',
                'Phone',
                'Status',
                'Last Login',
                'Created At',
                'Updated At'
            ];

            foreach ($users as $user) {
                // Handle last login formatting for CSV export
                $lastLoginFormatted = 'Never';
                if ($user->last_login_at) {
                    $lastLoginFormatted = $user->last_login_at->diffForHumans();
                }
                
                $csvData[] = [
                    $user->id,
                    $user->profile?->employee_id ?? '',
                    $user->name,
                    $user->email,
                    $user->formatted_role,
                    $user->profile?->department ?? '',
                    $user->profile?->position ?? '',
                    $user->profile?->phone ?? '',
                    $user->is_active ? 'Active' : 'Inactive',
                    $lastLoginFormatted,
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->updated_at->format('Y-m-d H:i:s')
                ];
            }

            // Generate CSV filename with timestamp
            $filename = 'users_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
            $filepath = storage_path('app/private/exports/' . $filename);
            
            // Ensure exports directory exists
            $directory = storage_path('app/private/exports');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Write CSV file
            $file = fopen($filepath, 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);

            return response()->download($filepath, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error exporting users: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to export users: ' . $e->getMessage());
        }
    }
}