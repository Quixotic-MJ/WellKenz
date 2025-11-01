<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('Auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        // Use the stored procedure for authentication
        $authResult = User::authenticate($request->username, $request->password);
        $result = json_decode($authResult, true);

        if ($result['success']) {
            $userData = $result['data'];
            
            // Get employee details using the stored procedure
            $employeeResult = json_decode(User::getUser($userData['user_id']), true);
            $employeeData = $employeeResult['success'] ? $employeeResult['data']['employee'] : null;

            session([
                'user_id' => $userData['user_id'],
                'username' => $userData['username'],
                'role' => $userData['role'],
                'emp_id' => $userData['emp_id'],
                'emp_name' => $employeeData['emp_name'] ?? 'Unknown',
                'emp_position' => $employeeData['emp_position'] ?? 'Unknown',
                'logged_in' => true
            ]);

            return $this->redirectToDashboard($userData['role']);
        }

        return back()->with('error', $result['message'] ?? 'Invalid username or password.');
    }

    public function logout()
    {
        session()->flush();
        return redirect('/')->with('success', 'You have been logged out.');
    }

    private function redirectToDashboard($role)
    {
        switch ($role) {
            case 'admin':
                return redirect()->route('Admin_dashboard');
            case 'employee':
                return redirect()->route('Staff_dashboard');
            case 'inventory':
                return redirect()->route('Inventory_dashboard');
            case 'purchasing':
                return redirect()->route('Purchasing_dashboard');
            case 'supervisor':
                return redirect()->route('Supervisor_dashboard');
            default:
                return redirect('/');
        }
    }
}