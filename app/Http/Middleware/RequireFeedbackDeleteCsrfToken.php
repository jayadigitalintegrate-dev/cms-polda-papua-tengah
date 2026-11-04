<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;

class RequireFeedbackDeleteCsrfToken
{
    public function handle(Request $request, Closure $next): Response
    {
        // Route-scoped: require a session token even for same-origin requests
        // and tests. Do not change the application's global CSRF middleware.
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
        $sessionToken = $request->hasSession() ? $request->session()->token() : null;

        if (! is_string($token) || ! is_string($sessionToken)
            || $sessionToken === '' || ! hash_equals($sessionToken, $token)) {
            throw new TokenMismatchException('CSRF token mismatch.');
        }

        return $next($request);
    }
}
