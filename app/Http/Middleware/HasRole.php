<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! auth()->check()) {
            abort(403, 'Unauthenticated.');
        }

        if (! auth()->user()->hasRole($role)) {
            abort(403, 'You do not have the required role to access this resource.');
        }

        return $next($request);
    }
}
