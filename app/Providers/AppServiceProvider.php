<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * Rate limit API publik (per IP).
         * Batas baca dibuat longgar karena satu halaman website memanggil
         * /api/news beberapa kali dan kantor dapat berbagi satu IP (NAT).
         */
        RateLimiter::for('public-read', function (Request $request) {
            return Limit::perMinute(300)->by($request->ip());
        });

        RateLimiter::for('public-complaints', function (Request $request) {
            return [
                Limit::perMinute(5)->by('complaints-min:' . $request->ip()),
                Limit::perHour(20)->by('complaints-hour:' . $request->ip()),
            ];
        });

        RateLimiter::for('public-ppid', function (Request $request) {
            return [
                Limit::perMinute(5)->by('ppid-min:' . $request->ip()),
                Limit::perHour(20)->by('ppid-hour:' . $request->ip()),
            ];
        });
    }
}
