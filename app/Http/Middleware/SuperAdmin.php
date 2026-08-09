<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdmin
{
    /**
     * Allow access only to authenticated superadmin users.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'superadmin') {
            abort(403, 'Akses hanya diperbolehkan untuk Superadmin.');
        }

        return $next($request);
    }
}
