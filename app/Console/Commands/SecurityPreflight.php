<?php

namespace App\Console\Commands;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Throwable;

/**
 * Pemeriksaan keamanan READ-ONLY sebelum/ sesudah deployment.
 *
 * Tidak mengubah konfigurasi, database, file, atau server.
 * Tidak pernah menampilkan nilai secret (hanya status ada/tidak).
 *
 * Exit code 1 bila ada FAIL.
 */
class SecurityPreflight extends Command
{
    protected $signature = 'security:preflight
        {--with-db : Jalankan pemeriksaan read-only ke MySQL (SHOW GRANTS, variabel server)}';

    protected $description = 'Read-only security preflight check (tidak mengubah apa pun)';

    /** @var array<int, array{string, string, string, string}> */
    private array $results = [];

    public function handle(): int
    {
        $production = app()->isProduction();

        $this->line('Environment: ' . app()->environment() . ($production ? '' : ' (bukan production: temuan production dilaporkan sebagai WARN)'));

        $this->checkApplication($production);
        $this->checkHttpLayer($production);
        $this->checkPhp($production);
        $this->checkDatabaseConfig($production);

        if ($this->option('with-db')) {
            $this->checkDatabaseServer($production);
        }

        $this->table(['Status', 'Area', 'Check', 'Detail'], $this->results);

        $count = fn (string $status) => count(array_filter($this->results, fn ($r) => $r[0] === $status));
        $fails = $count('FAIL');

        $this->line("Ringkasan: {$fails} FAIL, {$count('WARN')} WARN, {$count('PASS')} PASS, {$count('INFO')} INFO");

        return $fails > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function checkApplication(bool $production): void
    {
        $this->record(! config('app.debug'), $production, 'App', 'APP_DEBUG off', 'debug=' . (config('app.debug') ? 'true' : 'false'));
        $this->record(filled(config('app.key')), true, 'App', 'APP_KEY terisi', filled(config('app.key')) ? 'set' : 'kosong');
        $this->record(str_starts_with((string) config('app.url'), 'https://'), $production, 'App', 'APP_URL memakai https', parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: '-');
        $logLevel = (string) config('logging.channels.single.level', 'debug');
        $this->record($logLevel !== 'debug', $production, 'App', 'LOG_LEVEL bukan debug', $logLevel);

        $this->record((bool) config('session.secure'), $production, 'Session', 'Cookie secure (HTTPS only)', var_export((bool) config('session.secure'), true));
        $this->record((bool) config('session.http_only'), true, 'Session', 'Cookie http_only', var_export((bool) config('session.http_only'), true));
        $this->record(in_array(config('session.same_site'), ['lax', 'strict'], true), true, 'Session', 'Cookie same_site lax/strict', (string) config('session.same_site'));

        $this->record(! in_array(config('cache.default'), ['array', 'null'], true), $production, 'Cache', 'Cache store persisten (rate limiter)', (string) config('cache.default'));

        $this->record(is_dir(public_path('storage')), true, 'Storage', 'public/storage terhubung', is_dir(public_path('storage')) ? 'ada' : 'tidak ada');
    }

    private function checkHttpLayer(bool $production): void
    {
        $origins = (array) config('cors.allowed_origins');
        $this->record(! in_array('*', $origins, true), true, 'CORS', 'Origin bukan wildcard *', implode(', ', $origins) ?: '(kosong)');
        $this->record($origins !== [], true, 'CORS', 'Ada origin yang diizinkan', count($origins) . ' origin');
        $local = array_filter($origins, fn ($o) => str_contains($o, 'localhost') || str_contains($o, '127.0.0.1'));
        $this->record($local === [], $production, 'CORS', 'Tanpa origin localhost di production', $local === [] ? '-' : implode(', ', $local));

        foreach (['public-read', 'public-complaints', 'public-ppid'] as $limiter) {
            $this->record(RateLimiter::limiter($limiter) !== null, true, 'Rate limit', "Limiter {$limiter} terdaftar", RateLimiter::limiter($limiter) ? 'ada' : 'tidak ada');
        }

        foreach (['api/complaints' => 'POST', 'api/ppid-requests' => 'POST', 'api/news' => 'GET', 'forgot-password' => 'POST'] as $uri => $method) {
            $route = collect(Route::getRoutes()->getRoutes())->first(fn ($r) => $r->uri() === $uri && in_array($method, $r->methods(), true));
            $throttled = $route && collect($route->gatherMiddleware())->contains(fn ($m) => is_string($m) && str_starts_with($m, 'throttle:'));
            $this->record($throttled, true, 'Rate limit', "{$method} /{$uri} ber-throttle", $route ? ($throttled ? 'ya' : 'tidak') : 'route tidak ditemukan');
        }

        $hasHeaders = app(Kernel::class)->hasMiddleware(SecurityHeaders::class);
        $this->record($hasHeaders, true, 'Headers', 'SecurityHeaders middleware global', $hasHeaders ? 'aktif' : 'tidak aktif');
    }

    private function checkPhp(bool $production): void
    {
        /*
         * Baca nilai dari file php.ini yang dimuat (dipakai juga oleh SAPI web
         * bila memakai file yang sama). SAPI CLI meng-override beberapa nilai
         * (mis. max_execution_time=0), sehingga ini_get() tidak representatif.
         */
        $file = php_ini_loaded_file();
        $parsed = $file ? (parse_ini_file($file, false, INI_SCANNER_RAW) ?: []) : [];
        $ini = fn (string $key) => array_key_exists($key, $parsed) ? $parsed[$key] : get_cfg_var($key);

        $this->results[] = ['INFO', 'PHP', 'php.ini yang dibaca', $file ?: '(tidak ada)'];

        $this->record(! $this->iniOn($ini('expose_php')), $production, 'PHP', 'expose_php Off', $this->show($ini('expose_php')));
        $this->record(! $this->iniOn($ini('display_errors')), $production, 'PHP', 'display_errors Off', $this->show($ini('display_errors')));
        $this->record($this->bytes($ini('upload_max_filesize')) <= 20 * 1024 * 1024, $production, 'PHP', 'upload_max_filesize <= 20M', $this->show($ini('upload_max_filesize')));
        $this->record($this->bytes($ini('post_max_size')) <= 25 * 1024 * 1024, $production, 'PHP', 'post_max_size <= 25M', $this->show($ini('post_max_size')));

        $maxExec = (int) $ini('max_execution_time');
        $this->record($maxExec > 0 && $maxExec <= 120, $production, 'PHP', 'max_execution_time 1-120 detik', $this->show($ini('max_execution_time')));
        $this->record($this->iniOn($ini('session.use_strict_mode')), $production, 'PHP', 'session.use_strict_mode On', $this->show($ini('session.use_strict_mode')));
    }

    private function checkDatabaseConfig(bool $production): void
    {
        $connection = config('database.default');
        $username = (string) config("database.connections.{$connection}.username");
        $password = config("database.connections.{$connection}.password");

        if (! in_array(config("database.connections.{$connection}.driver"), ['mysql', 'mariadb'], true)) {
            $this->results[] = ['INFO', 'Database', 'Driver', (string) config("database.connections.{$connection}.driver")];

            return;
        }

        $this->record($username !== 'root', $production, 'Database', 'Aplikasi tidak memakai user root', $username === 'root' ? 'root' : 'bukan root');
        $this->record($password !== null && $password !== '', true, 'Database', 'Password database terisi', ($password !== null && $password !== '') ? 'set' : 'kosong');
    }

    private function checkDatabaseServer(bool $production): void
    {
        try {
            $grants = collect(DB::select('SHOW GRANTS'))->map(fn ($g) => (string) array_values((array) $g)[0]);
            $broad = $grants->contains(fn ($g) => preg_match('/ALL PRIVILEGES|GRANT OPTION|\bSUPER\b|\bFILE\b|\bSHUTDOWN\b/i', $g) === 1);
            $this->record(! $broad, $production, 'Database', 'User aplikasi least-privilege', $broad ? 'memiliki hak luas (FILE/SHUTDOWN/GRANT/ALL)' : 'terbatas');

            $vars = collect(DB::select(
                "SELECT VARIABLE_NAME AS n, VARIABLE_VALUE AS v FROM performance_schema.global_variables WHERE VARIABLE_NAME IN ('bind_address', 'mysqlx_bind_address', 'secure_file_priv', 'local_infile')"
            ))->pluck('v', 'n');

            foreach (['bind_address', 'mysqlx_bind_address'] as $name) {
                $value = (string) ($vars[$name] ?? '');
                $this->record(self::isLoopbackBindAddress($value), $production, 'Database', "{$name} hanya localhost", $value ?: '-');
            }

            $this->record(($vars['secure_file_priv'] ?? '') !== '', $production, 'Database', 'secure_file_priv dibatasi', ($vars['secure_file_priv'] ?? '') === '' ? 'kosong (tanpa batas)' : 'dibatasi');
            $this->record(($vars['local_infile'] ?? 'OFF') === 'OFF', true, 'Database', 'local_infile OFF', (string) ($vars['local_infile'] ?? '-'));
        } catch (Throwable $e) {
            $this->results[] = ['WARN', 'Database', 'Pemeriksaan server MySQL', 'tidak dapat dibaca: ' . class_basename($e)];
        }
    }

    /**
     * True bila bind_address / mysqlx_bind_address hanya berisi alamat loopback.
     * MySQL 8.0.13+ menerima beberapa alamat dipisah koma (mis. "127.0.0.1,::1");
     * setiap entry harus loopback. Nilai kosong tidak dianggap loopback.
     */
    public static function isLoopbackBindAddress(string $value): bool
    {
        $entries = array_map('trim', explode(',', $value));

        foreach ($entries as $entry) {
            if (! in_array($entry, ['127.0.0.1', '::1', 'localhost'], true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * PASS bila $ok. Bila tidak: FAIL untuk pemeriksaan wajib, WARN untuk
     * pemeriksaan khusus production saat environment bukan production.
     */
    private function record(bool $ok, bool $enforced, string $area, string $check, string $detail): void
    {
        $this->results[] = [$ok ? 'PASS' : ($enforced ? 'FAIL' : 'WARN'), $area, $check, $detail];
    }

    private function iniOn(mixed $value): bool
    {
        return in_array(strtolower((string) $value), ['1', 'on', 'true', 'yes'], true);
    }

    private function show(mixed $value): string
    {
        return $value === false ? '(tidak diatur)' : (string) $value;
    }

    private function bytes(mixed $value): int
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '-1') {
            return PHP_INT_MAX;
        }

        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => (int) $value,
        };
    }
}
