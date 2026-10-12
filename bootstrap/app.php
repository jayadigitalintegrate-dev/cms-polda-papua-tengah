<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Middleware\RedirectProxiedHttpToHttps;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SuperAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'superadmin' => SuperAdmin::class,
        ]);

        TrustProxies::at('REMOTE_ADDR');

        /*
         * Hanya X-Forwarded-For dan X-Forwarded-Proto yang dipercaya.
         * X-Forwarded-Host/Port/Prefix dapat dikirim klien melalui Cloudflare,
         * sehingga nama host selalu diambil dari header Host.
         */
        TrustProxies::withHeaders(
            Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->append(SecurityHeaders::class);
        $middleware->append(RedirectProxiedHttpToHttps::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
