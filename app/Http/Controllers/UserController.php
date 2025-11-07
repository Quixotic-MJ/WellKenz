<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('employee')->get();
        $employees = Employee::where('emp_status', 'active')
            ->whereDoesntHave('user')
            ->get();

        $totalUsers = User::count();
        $activeUsers = User::whereHas('employee', function ($query) {
            $query->where('emp_status', 'active');
        })->count();
        $adminsCount = User::where('role', 'admin')->count();
        $inactiveUsers = User::whereHas('employee', function ($query) {
            $query->where('emp_status', 'inactive');
        })->count();

        return view('Admin.Management.user_management', compact(
            'users',
            'employees',
            'totalUsers',
            'activeUsers',
            'adminsCount',
            'inactiveUsers'
        ));
    }

    public function show($id)
    {
        $user = User::with('employee')->findOrFail($id);
        return response()->json($user);
    }

    public function edit($id)
    {
        $user = User::with('employee')->findOrFail($id);
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|unique:users,username|max:50',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:admin,employee,inventory,purchasing,supervisor',
            'emp_id' => 'required|exists:employees,emp_id|unique:users,emp_id',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'emp_id' => $validated['emp_id'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully!',
                'user' => $user->load('employee')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => [
                'required',
                'max:50',
                Rule::unique('users', 'username')->ignore($user->user_id, 'user_id')
            ],
            'role' => 'required|in:admin,employee,inventory,purchasing,supervisor',
            // Remove emp_id validation since we're not allowing it to be changed
        ]);

        try {
            DB::beginTransaction();

            $user->update([
                'username' => $validated['username'],
                'role' => $validated['role'],
                // Don't update emp_id since we're not allowing it to be changed
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully!',
                'user' => $user->load('employee')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        try {
            DB::beginTransaction();

            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating password: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // Check if user has related records
            if (
                $user->requisitions()->exists() ||
                $user->approvedRequisitions()->exists() ||
                $user->inventoryTransactions()->exists() ||
                $user->issuedAcknowledgeReceipts()->exists() ||
                $user->receivedAcknowledgeReceipts()->exists() ||
                $user->memos()->exists()
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete user with existing related records. Please deactivate instead.'
                ], 422);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resetPassword($id)
    {
        try {
            $user = User::findOrFail($id);

            // Generate a temporary password
            $tempPassword = 'wellkenz_' . rand(1000, 9999);

            $user->update([
                'password' => Hash::make($tempPassword)
            ]);

            // In a real application, you would send this via email
            // Mail::to($user->employee->emp_email)->send(new PasswordResetMail($tempPassword));

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully! Temporary password: ' . $tempPassword,
                'temp_password' => $tempPassword // Remove this in production
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error resetting password: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $user = User::with('employee')->findOrFail($id);

            DB::beginTransaction();

            $newStatus = $user->employee->emp_status === 'active' ? 'inactive' : 'active';

            $user->employee->update([
                'emp_status' => $newStatus
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User ' . $newStatus . 'd successfully!',
                'new_status' => $newStatus
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating user status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $search = $request->get('search', '');

        $users = User::with('employee')
            ->where('username', 'like', "%{$search}%")
            ->orWhereHas('employee', function ($query) use ($search) {
                $query->where('emp_name', 'like', "%{$search}%")
                    ->orWhere('emp_position', 'like', "%{$search}%");
            })
            ->get();

        return response()->json($users);
    }
}
