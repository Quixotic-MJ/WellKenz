<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $user = Auth::user();

        // 1. Check Boolean is_active (Matches Schema)
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Your account has been deactivated.');
        }

        // 2. Role Check
        // If the route requires 'admin', but user is 'employee', abort.
        if ($user->role !== $role) {
            abort(403, 'Unauthorized access. You are logged in as ' . ucfirst($user->role) . ', but this page requires ' . ucfirst($role) . '.');
        }

        return $next($request);
    }
}