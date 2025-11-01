<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SupervisorOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (session('role') !== 'supervisor') {
            return redirect()->back()->with('error', 'Access denied. Supervisor privileges required.');
        }

        return $next($request);
    }
}