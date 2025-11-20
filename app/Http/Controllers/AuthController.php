<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User; 

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('Auth.login');
    }

    public function login(Request $request)
    {
        // 1. Validate Email instead of Username
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // 2. Fetch User
        $user = User::where('email', $credentials['email'])->first();

        // 3. Check Password against 'password_hash' column from your Schema
        // Note: Standard Laravel uses 'password', but your schema uses 'password_hash'
        if ($user && Hash::check($credentials['password'], $user->password_hash)) {
            
            // 4. Check if user is active (Boolean check based on schema)
            if (!$user->is_active) {
                return back()->with('error', 'Your account is deactivated. Please contact the Admin.')->withInput();
            }

            // Log the user in
            Auth::login($user);
            
            // Store role in session for easy access
            session(['role' => $user->role]);
            session(['user_name' => $user->name]);
            
            // 5. Redirect based on the 5 defined roles
            return $this->redirectToDashboard($user->role);
        }

        return back()->with('error', 'Invalid email or password. Please try again.')->withInput();
    }

    private function redirectToDashboard($role)
    {
        // Matches the 'user_role' ENUM in your database
        switch ($role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'supervisor':
                return redirect()->route('supervisor.dashboard');
            case 'purchasing':
                return redirect()->route('purchasing.dashboard');
            case 'inventory':
                return redirect()->route('inventory.dashboard');
            case 'employee': // This is the Baker
                return redirect()->route('employee.dashboard');
            default:
                Auth::logout();
                return redirect()->route('login')->with('error', 'Role not recognized. Contact Admin.');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}