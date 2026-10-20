<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Middleware\RedirectProxiedHttpToHttps;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SuperAdmin;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\ViewException;

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

        /*
         * HIGH-01: tidak ada TrustProxies::at(). Proxy tepercaya hanya berasal
         * dari config('trustedproxy.proxies') (TRUSTED_PROXIES, divalidasi
         * App\Support\TrustedProxyList; selalu array, [] = tanpa proxy).
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

        /*
         * HIGH-02: pesan QueryException memuat SQL dengan nilai binding (NIK,
         * nama, telepon, email, alamat, isi pengaduan) serta host/port/nama
         * database. Yang dicatat hanya kategori tetap, SQLSTATE, dan kode
         * error driver yang formatnya sudah divalidasi. ->stop() menghentikan
         * pelaporan bawaan Handler yang menulis pesan lengkap dan stack trace.
         */
        $exceptions->report(function (QueryException $e): void {
            $info = is_array($e->errorInfo) ? $e->errorInfo : [];

            $sqlState = $info[0] ?? null;
            $sqlState = is_string($sqlState) && preg_match('/\A[0-9A-Z]{5}\z/', $sqlState) === 1
                ? $sqlState
                : 'UNKNOWN';

            $driverCode = $info[1] ?? null;
            $driverCode = is_int($driverCode) || (is_string($driverCode) && preg_match('/\A[0-9]{1,10}\z/', $driverCode) === 1)
                ? (int) $driverCode
                : 'UNKNOWN';

            try {
                Log::error('DATABASE_QUERY_FAILURE', [
                    'sqlstate' => $sqlState,
                    'driver_code' => $driverCode,
                ]);
            } catch (Throwable) {
                // Kegagalan logger tidak boleh memicu pelaporan bawaan berisi data sensitif.
            }
        })->stop();

        /*
         * HIGH-02 R1B: query yang gagal saat Blade dirender dibungkus menjadi
         * ViewException yang menyalin pesan QueryException (SQL + binding),
         * sehingga reporter di atas tidak menangkapnya. Bila rantai previous
         * memuat QueryException, catat metadata aman saja lalu return false
         * (hentikan pelaporan bawaan). ViewException lain: return null, sehingga
         * pelaporan bawaan berjalan seperti biasa. Sengaja tanpa ->stop().
         */
        $exceptions->report(function (ViewException $e): ?bool {
            $query = null;
            $previous = $e->getPrevious();

            for ($depth = 0; $previous !== null; $depth++) {
                if ($depth >= 32) {
                    // Rantai tidak dapat dipastikan berakhir: fail-closed.
                    try {
                        Log::error('VIEW_EXCEPTION_CHAIN_UNVERIFIED');
                    } catch (Throwable) {
                        // Kegagalan logger tidak boleh memicu pelaporan bawaan.
                    }

                    return false;
                }

                if ($previous instanceof QueryException) {
                    $query = $previous;
                    break;
                }

                $previous = $previous->getPrevious();
            }

            if ($query === null) {
                return null;
            }

            $info = is_array($query->errorInfo) ? $query->errorInfo : [];

            $sqlState = $info[0] ?? null;
            $sqlState = is_string($sqlState) && preg_match('/\A[0-9A-Z]{5}\z/', $sqlState) === 1
                ? $sqlState
                : 'UNKNOWN';

            $driverCode = $info[1] ?? null;
            $driverCode = is_int($driverCode) || (is_string($driverCode) && preg_match('/\A[0-9]{1,10}\z/', $driverCode) === 1)
                ? (int) $driverCode
                : 'UNKNOWN';

            try {
                Log::error('VIEW_DATABASE_QUERY_FAILURE', [
                    'sqlstate' => $sqlState,
                    'driver_code' => $driverCode,
                ]);
            } catch (Throwable) {
                // Kegagalan logger tidak boleh memicu pelaporan bawaan berisi data sensitif.
            }

            return false;
        });
    })->create();
