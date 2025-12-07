<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLoggingMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Logging is now handled by the Auditable trait on the models directly.
        return $next($request);
    }
}