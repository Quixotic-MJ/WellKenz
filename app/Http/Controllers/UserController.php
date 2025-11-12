<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $adminsCount = User::where('role', 'admin')->count();
        $inactiveUsers = User::where('status', 'inactive')->count();

        return view('Admin.User.user_management', compact(
            'users',
            'totalUsers',
            'activeUsers',
            'adminsCount',
            'inactiveUsers'
        ));
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|unique:users,username|max:50',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:admin,employee,inventory,purchasing,supervisor',
            'name' => 'required|string|max:100',
            'position' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'contact' => 'required|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'name' => $validated['name'],
                'position' => $validated['position'],
                'email' => $validated['email'],
                'contact' => $validated['contact'],
                'status' => 'active', // Default status
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully!',
                'user' => $user
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
            'name' => 'required|string|max:100',
            'position' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->user_id, 'user_id')
            ],
            'contact' => 'required|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $user->update([
                'username' => $validated['username'],
                'role' => $validated['role'],
                'name' => $validated['name'],
                'position' => $validated['position'],
                'email' => $validated['email'],
                'contact' => $validated['contact'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully!',
                'user' => $user
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

            return redirect()->back()->with('success', 'Password updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating password: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // Check if user has related records
            // Note: You'll need to update these relationships in your User model
            if (
                $user->requisitions()->exists() ||
                $user->approvedRequisitions()->exists()
                // $user->inventoryTransactions()->exists() ||
                // $user->issuedAcknowledgeReceipts()->exists() ||
                // $user->receivedAcknowledgeReceipts()->exists() ||
                // $user->memos()->exists()
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
            // Mail::to($user->email)->send(new PasswordResetMail($tempPassword));

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
            $user = User::findOrFail($id);

            DB::beginTransaction();

            $newStatus = $user->status === 'active' ? 'inactive' : 'active';

            $user->update([
                'status' => $newStatus
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

        $users = User::where('username', 'like', "%{$search}%")
            ->orWhere('name', 'like', "%{$search}%")
            ->orWhere('position', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->orWhere('contact', 'like', "%{$search}%")
            ->orWhere('role', 'like', "%{$search}%")
            ->get();

        return response()->json($users);
    }
}