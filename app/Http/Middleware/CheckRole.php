<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! $request->user()) {
            if ($request->expectsJson()) {
                 return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect('login');
        }

        $userRole = $request->user()->accountType ? $request->user()->accountType->name : null;

        // If no specific roles are required, just pass (should usually rely on auth middleware)
        if (empty($roles)) {
            return $next($request);
        }

        // Allow 'Owner' to access everything 'Admin' can, effectively? 
        // Or strictly check? The prompt says "Enable 'Owner' to access all 'Admin' management pages".
        // So if the route requires 'Admin', 'Owner' should also pass?
        // Better to be explicit in route definition: middleware('role:Admin,Owner')
        
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // Optional: Fail gracefully or redirect
        if ($request->expectsJson()) {
             return response()->json(['message' => 'Unauthorized.'], 403);
        }
        
        abort(403, 'Unauthorized access.');
    }
}
