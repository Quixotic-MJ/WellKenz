<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class SupervisorOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || Auth::user()->role !== 'supervisor') {
            return redirect()->route('login')->with('error', 'Access denied. Supervisor privileges required.');
        }

        return $next($request);
    }
}