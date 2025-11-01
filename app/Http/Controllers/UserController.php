<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        try {
            // Get all users using stored procedures (individual calls)
            $users = User::all();
            $usersWithDetails = [];
            
            foreach ($users as $user) {
                $userResult = json_decode(User::getUser($user->user_id), true);
                if ($userResult['success']) {
                    $usersWithDetails[] = $userResult['data'];
                }
            }
            
            // Get employees without user accounts
            $employees = Employee::doesntHave('user')->get();
            
            // Get all departments
            $departments = Department::all();
            
            // Calculate statistics
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::count(),
                'admin_users' => User::where('role', 'admin')->count(),
                'pending_users' => 0,
            ];

            $roles = ['admin', 'employee', 'purchasing', 'inventory', 'supervisor'];

            return view('Admin.user', compact('usersWithDetails', 'employees', 'departments', 'stats', 'roles'));

        } catch (\Exception $e) {
            return view('Admin.user', [
                'usersWithDetails' => [],
                'employees' => collect(),
                'departments' => collect(),
                'stats' => [
                    'total_users' => 0,
                    'active_users' => 0,
                    'admin_users' => 0,
                    'pending_users' => 0,
                ],
                'roles' => ['admin', 'employee', 'purchasing', 'inventory', 'supervisor']
            ]);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string',
            'emp_id' => 'required|exists:employees,emp_id'
        ]);

        try {
            // Use stored procedure
            $result = User::createUser($request->all());
            $resultData = json_decode($result, true);

            if ($resultData['success']) {
                return redirect()->route('Admin_user')->with('success', $resultData['message']);
            } else {
                return redirect()->route('Admin_user')->with('error', $resultData['message']);
            }
        } catch (\Exception $e) {
            return redirect()->route('Admin_user')->with('error', 'Error creating user: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            // Use stored procedure
            $result = User::getUser($id);
            $resultData = json_decode($result, true);

            if (!$resultData['success']) {
                return redirect()->route('Admin_user')->with('error', $resultData['message']);
            }

            $user = (object) $resultData['data'];
            $employees = Employee::all();
            $roles = ['admin', 'employee', 'purchasing', 'inventory', 'supervisor'];
            
            return view('Admin.user-edit', compact('user', 'employees', 'roles'));
        } catch (\Exception $e) {
            return redirect()->route('Admin_user')->with('error', 'Error retrieving user: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'username' => 'required|string|unique:users,username,' . $id . ',user_id',
            'role' => 'required|string',
            'emp_id' => 'required|exists:employees,emp_id'
        ]);

        try {
            $data = [
                'username' => $request->username,
                'role' => $request->role,
                'emp_id' => $request->emp_id
            ];
            
            // Use stored procedure for update
            $result = User::updateUser($id, $data);
            $resultData = json_decode($result, true);

            // If password is provided, update it using stored procedure
            if ($request->filled('password')) {
                $passwordResult = User::changePassword($id, $request->password);
                $passwordData = json_decode($passwordResult, true);
                
                if (!$passwordData['success']) {
                    return redirect()->route('Admin_user')->with('error', 'User updated but password change failed: ' . $passwordData['message']);
                }
            }

            if ($resultData['success']) {
                return redirect()->route('Admin_user')->with('success', $resultData['message']);
            } else {
                return redirect()->route('Admin_user')->with('error', $resultData['message']);
            }
        } catch (\Exception $e) {
            return redirect()->route('Admin_user')->with('error', 'Error updating user: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            // Use stored procedure
            $result = User::deleteUser($id);
            $resultData = json_decode($result, true);

            if ($resultData['success']) {
                return redirect()->route('Admin_user')->with('success', $resultData['message']);
            } else {
                return redirect()->route('Admin_user')->with('error', $resultData['message']);
            }
        } catch (\Exception $e) {
            return redirect()->route('Admin_user')->with('error', 'Error deleting user: ' . $e->getMessage());
        }
    }
}