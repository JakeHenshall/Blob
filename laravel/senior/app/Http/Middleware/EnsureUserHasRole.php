<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route-level role gate.
 *
 * Usage:
 *   Route::get('/admin', fn () => ...)->middleware('role:admin');
 *   Route::get('/staff', fn () => ...)->middleware('role:admin,manager');
 *
 * Aborts with 403 when the authenticated user's role is not in the
 * accepted list, or 403 when no user is present.
 */
class EnsureUserHasRole
{
    /**
     * @param  string  ...$roles  accepted role string values (see {@see \App\Support\Role})
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role->value, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
