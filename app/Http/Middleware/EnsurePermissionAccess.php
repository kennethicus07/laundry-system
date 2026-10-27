<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 ** Middleware to ensure that the super admin user has all permission .
 ** 
 */


class EnsurePermissionAccess
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();

        if ($user && $user->hasRole('super-admin')) {
            return $next($request);
        }

        if ($user && $user->can($permission)) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}
