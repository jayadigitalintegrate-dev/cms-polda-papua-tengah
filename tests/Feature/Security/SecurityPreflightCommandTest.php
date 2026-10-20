<?php

namespace Tests\Feature\Security;

use App\Console\Commands\SecurityPreflight;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SubclassedTrustProxies extends TrustProxies
{
    protected $proxies = '*';
}

class SecurityPreflightCommandTest extends SecurityTestCase
{
    /** Menjalankan preflight dan mengembalikan [exit code, output tabel]. */
    private function preflight(array $proxyConfig = []): array
    {
        config(collect($proxyConfig)->mapWithKeys(fn ($value, $key) => ["trustedproxy.{$key}" => $value])->all());

        $exit = Artisan::call('security:preflight');

        return [$exit, Artisan::output()];
    }

    private function assertRow(string $status, string $check, string $output): void
    {
        $this->assertMatchesRegularExpression('/\|\s*' . $status . '\s*\|\s*Proxy\s*\|\s*' . preg_quote($check, '/') . '/', $output);
    }

    public function test_default_proxy_config_passes_with_mode_warning_outside_production(): void
    {
        [$exit, $output] = $this->preflight();

        $this->assertSame(0, $exit);
        $this->assertRow('PASS', 'Trusted proxy berupa daftar IP/CIDR kanonik', $output);
        $this->assertRow('PASS', 'TrustProxies::at() tidak menimpa config', $output);
        $this->assertRow('PASS', 'Header dipercaya hanya X-Forwarded-For/Proto', $output);
        $this->assertRow('PASS', 'TrustProxies (kelas asli) di stack global', $output);
        $this->assertRow('WARN', 'TRUSTED_PROXY_MODE dinyatakan eksplisit', $output);
        $this->assertRow('PASS', 'Mode konsisten dengan daftar proxy', $output);
    }

    public function test_consistent_proxied_config_passes(): void
    {
        [$exit, $output] = $this->preflight(['proxies' => ['198.51.100.10'], 'mode' => 'proxied', 'mode_source' => 'declared']);

        $this->assertSame(0, $exit);
        $this->assertRow('PASS', 'TRUSTED_PROXY_MODE dinyatakan eksplisit', $output);
        $this->assertRow('PASS', 'Mode konsisten dengan daftar proxy', $output);
    }

    /** F1: daftar dengan segmen ditolak -> FAIL. */
    public function test_rejected_segments_fail(): void
    {
        [$exit, $output] = $this->preflight(['rejected' => [['position' => 2, 'reason' => 'FORBIDDEN_ALIAS']], 'mode' => 'proxied', 'mode_source' => 'declared']);

        $this->assertSame(1, $exit);
        $this->assertRow('FAIL', 'TRUSTED_PROXIES tanpa segmen ditolak (fail-closed)', $output);
        $this->assertStringContainsString('#2:FORBIDDEN_ALIAS', $output);
    }

    /** F1: daftar tidak kanonik (mis. ditulis langsung ke config) -> FAIL. */
    public function test_non_canonical_proxy_list_fails(): void
    {
        foreach ([null, '*', ['*'], ['10.0.0.1/8'], ['::ffff:198.51.100.10']] as $proxies) {
            [$exit, $output] = $this->preflight(['proxies' => $proxies]);

            $this->assertSame(1, $exit, var_export($proxies, true));
            $this->assertRow('FAIL', 'Trusted proxy berupa daftar IP/CIDR kanonik', $output);
        }
    }

    /** F1: TrustProxies::at() statis menimpa config -> FAIL. */
    public function test_static_at_override_fails(): void
    {
        app(Kernel::class);
        TrustProxies::at('*');

        [$exit, $output] = $this->preflight();

        $this->assertSame(1, $exit);
        $this->assertRow('FAIL', 'TrustProxies::at() tidak menimpa config', $output);
    }

    /** F1: header dipercaya lebih luas dari XFF/XFP -> FAIL. */
    public function test_broader_trusted_headers_fail(): void
    {
        app(Kernel::class);
        TrustProxies::withHeaders(Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_HOST);

        [$exit, $output] = $this->preflight();

        $this->assertSame(1, $exit);
        $this->assertRow('FAIL', 'Header dipercaya hanya X-Forwarded-For/Proto', $output);
    }

    /** F1 + G2-02: subclass TrustProxies di stack -> FAIL meskipun kelas asli ada. */
    public function test_trust_proxies_subclass_in_stack_fails(): void
    {
        app(Kernel::class)->pushMiddleware(SubclassedTrustProxies::class);

        [$exit, $output] = $this->preflight();

        $this->assertSame(1, $exit);
        $this->assertRow('FAIL', 'TrustProxies (kelas asli) di stack global', $output);
        $this->assertStringContainsString('subclass TrustProxies terdaftar', $output);
    }

    /** F1 + G2-02: TrustProxies tidak ada di stack -> FAIL. */
    public function test_missing_trust_proxies_in_stack_fails(): void
    {
        $kernel = app(Kernel::class);
        $kernel->setGlobalMiddleware(array_values(array_filter($kernel->getGlobalMiddleware(), fn ($m) => $m !== TrustProxies::class)));

        [$exit, $output] = $this->preflight();

        $this->assertSame(1, $exit);
        $this->assertRow('FAIL', 'TrustProxies (kelas asli) di stack global', $output);
    }

    /** G2-02: Kernel atau stack yang tidak dapat diverifikasi -> bukan 'ok'. */
    public function test_stack_check_is_fail_closed(): void
    {
        $this->assertSame('ok', SecurityPreflight::trustProxiesInGlobalStack(app(Kernel::class)));
        $this->assertSame('unverifiable', SecurityPreflight::trustProxiesInGlobalStack(null));
        $this->assertSame('unverifiable', SecurityPreflight::trustProxiesInGlobalStack(new \stdClass));
    }

    /** F1 + G2-01/G2-03: mode invalid -> FAIL di semua environment, tanpa PASS "dinyatakan eksplisit". */
    public function test_invalid_mode_fails_without_explicit_declaration_pass(): void
    {
        [$exit, $output] = $this->preflight(['mode' => 'direct', 'mode_source' => 'invalid']);

        $this->assertSame(1, $exit);
        $this->assertRow('FAIL', 'TRUSTED_PROXY_MODE bernilai direct/proxied', $output);
        $this->assertStringNotContainsString('dinyatakan eksplisit', $output);
    }

    /** F2: mode tidak disetel -> FAIL di production. */
    public function test_unset_mode_fails_in_production(): void
    {
        $this->app['env'] = 'production';

        [$exit, $output] = $this->preflight();

        $this->assertSame(1, $exit);
        $this->assertRow('FAIL', 'TRUSTED_PROXY_MODE dinyatakan eksplisit', $output);
    }

    /** F2: mode tidak konsisten dengan daftar -> FAIL. */
    public function test_mode_inconsistent_with_list_fails(): void
    {
        foreach ([[[], 'proxied'], [['198.51.100.10'], 'direct']] as [$proxies, $mode]) {
            [$exit, $output] = $this->preflight(['proxies' => $proxies, 'mode' => $mode, 'mode_source' => 'declared']);

            $this->assertSame(1, $exit, $mode);
            $this->assertRow('FAIL', 'Mode konsisten dengan daftar proxy', $output);
        }
    }

    /** F4: alamat proxy tidak dicetak. */
    public function test_proxy_addresses_are_not_printed(): void
    {
        [, $output] = $this->preflight(['proxies' => ['198.51.100.123', '2001:db8:abcd::/48'], 'mode' => 'proxied', 'mode_source' => 'declared']);

        $this->assertStringNotContainsString('198.51.100.123', $output);
        $this->assertStringNotContainsString('2001:db8:abcd', $output);
    }

    /** F5: properti reflection yang hilang tidak melempar exception. */
    public function test_missing_reflection_property_is_reported_not_thrown(): void
    {
        $this->assertSame(['found' => false, 'value' => null], SecurityPreflight::trustProxiesStatic('propertiTidakAda'));
        $this->assertSame(['found' => false, 'value' => null], SecurityPreflight::trustProxiesStatic('proxies'));
        $this->assertTrue(SecurityPreflight::trustProxiesStatic('alwaysTrustHeaders')['found']);
    }

    public function test_preflight_runs_read_only_and_reports_checks(): void
    {
        $this->artisan('security:preflight')
            ->expectsOutputToContain('Ringkasan:')
            ->assertExitCode(0);
    }

    public function test_preflight_fails_when_production_runs_with_debug(): void
    {
        $this->app['env'] = 'production';
        config(['app.debug' => true]);

        $this->artisan('security:preflight')->assertExitCode(1);
    }

    public function test_preflight_fails_when_cors_allows_any_origin(): void
    {
        config(['cors.allowed_origins' => ['*']]);

        $this->artisan('security:preflight')->assertExitCode(1);
    }

    public function test_preflight_never_prints_secret_values(): void
    {
        config(['app.key' => 'base64:SECRET-KEY-SHOULD-NOT-APPEAR']);

        $this->artisan('security:preflight')
            ->doesntExpectOutputToContain('SECRET-KEY-SHOULD-NOT-APPEAR');
    }

    public function test_loopback_only_bind_addresses_are_accepted(): void
    {
        foreach (['127.0.0.1', '::1', 'localhost', '127.0.0.1,::1', '::1,127.0.0.1', '127.0.0.1, ::1'] as $value) {
            $this->assertTrue(SecurityPreflight::isLoopbackBindAddress($value), "expected loopback: '{$value}'");
        }
    }

    public function test_bind_addresses_with_any_non_loopback_entry_are_rejected(): void
    {
        foreach (['0.0.0.0', '*', '::', '127.0.0.1,0.0.0.0', '::,127.0.0.1', '192.168.1.11', ''] as $value) {
            $this->assertFalse(SecurityPreflight::isLoopbackBindAddress($value), "expected non-loopback: '{$value}'");
        }
    }
}
