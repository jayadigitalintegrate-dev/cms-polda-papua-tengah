<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class RedirectProxiedHttpToHttps
{
    /**
     * Alihkan permintaan HTTP yang datang melalui reverse proxy ke HTTPS,
     * hanya untuk hostname yang diizinkan secara eksplisit.
     *
     * Akses langsung tanpa proxy (mis. http://127.0.0.1:8000) tidak membawa
     * X-Forwarded-Proto dan tidak dialihkan agar pengujian lokal tetap berjalan.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedHosts = (array) config('security.https_redirect_hosts', []);

        if ($allowedHosts === []
            || ! $request->headers->has('X-Forwarded-Proto')
            || $request->isSecure()) {
            return $next($request);
        }

        $index = array_search($request->getHost(), $allowedHosts, true);

        if ($index === false) {
            abort(400);
        }

        return new RedirectResponse(
            'https://' . $allowedHosts[$index] . $request->getRequestUri(),
            $request->isMethodSafe() ? 302 : 307
        );
    }
}
