<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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

        /*
         * Kritik & Saran boleh anonim, sehingga batasnya lebih ketat
         * daripada pengaduan dan PPID.
         */
        RateLimiter::for('public-feedback', function (Request $request) {
            return [
                Limit::perMinute(3)->by('feedback-min:' . $request->ip()),
                Limit::perHour(10)->by('feedback-hour:' . $request->ip()),
            ];
        });

        /*
         * Dashboard Kritik & Saran (berisi identitas opsional pengirim).
         * Melihat: semua role CMS yang sah, seperti modul Pengaduan/PPID.
         * Mengubah status/catatan: superadmin dan operator saja. Hak ubah
         * supervisi belum disahkan (F-07), sehingga sementara dibatasi baca.
         * Akun dengan role lain ditolak.
         */
        Gate::define('view-feedback', function (User $user): bool {
            return in_array($user->role, ['superadmin', 'operator', 'supervisi'], true);
        });

        Gate::define('update-feedback', function (User $user): bool {
            return in_array($user->role, ['superadmin', 'operator'], true);
        });

        Gate::define('delete-feedback', fn (User $user): bool => $user->role === 'superadmin');
        /*
         * Hak akses modul Pengaduan Masyarakat.
         * Supervisi hanya membaca; penghapusan khusus superadmin.
         */
        Gate::define('view-complaints', function (User $user): bool {
            return in_array($user->role, ['superadmin', 'operator', 'supervisi'], true);
        });

        Gate::define('update-complaints', function (User $user): bool {
            return in_array($user->role, ['superadmin', 'operator'], true);
        });

        Gate::define('delete-complaints', function (User $user): bool {
            return $user->role === 'superadmin';
        });
    }
}
