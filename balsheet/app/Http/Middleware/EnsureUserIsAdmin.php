<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Deny access unless the authenticated user is an administrator.
        if (! $request->user() || ! $request->user()->isAdmin()) {
            abort(403, 'Unauthorized. Administrator access is required.');
        }

        return $next($request);
    }
}
