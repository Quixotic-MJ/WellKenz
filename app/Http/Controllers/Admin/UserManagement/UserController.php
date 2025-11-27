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

        return view('Admin.user_management.all_user', compact('users', 'roleStats'));
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
}