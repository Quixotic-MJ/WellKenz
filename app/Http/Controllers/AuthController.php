<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // This is no longer strictly needed for this file
use App\Models\User; // This is now used

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('Auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $credentials['username'])->first();

        // Check if user exists, password is correct, and user is active
        if ($user && Hash::check($credentials['password'], $user->password)) {
            // Check if user is active
            if ($user->status !== 'active') {
                return back()->with('error', 'Your account has been deactivated. Please contact administrator.')->withInput();
            }

            Auth::login($user);
            
            // Store role in session for individual middleware
            session(['role' => $user->role]);
            session(['user_name' => $user->name]);
            session(['user_position' => $user->position]);
            
            // Redirect based on user role
            return $this->redirectToDashboard($user->role);
        }

        return back()->with('error', 'Invalid credentials. Please try again.')->withInput();
    }

    private function redirectToDashboard($role)
    {
        switch ($role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'employee':
                return redirect()->route('staff.dashboard');
            case 'inventory':
                return redirect()->route('inventory.dashboard');
            case 'purchasing':
                return redirect()->route('purchasing.dashboard');
            case 'supervisor':
                return redirect()->route('supervisor.dashboard');
            default:
                return redirect()->route('login')->with('error', 'Unknown user role.');
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