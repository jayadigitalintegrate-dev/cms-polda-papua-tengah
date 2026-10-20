<?php

namespace Tests\Feature\Security;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/**
 * HIGH-01/D1: perilaku runtime TrustProxies dengan daftar proxy eksplisit
 * (config trustedproxy.proxies) dan interaksinya dengan redirect HTTPS.
 * PASS di sini membuktikan perilaku aplikasi, bukan topologi produksi (C1-C3).
 */
class TrustedProxyTest extends SecurityTestCase
{
    private const PROXY = '198.51.100.10';
    private const CLIENT = '203.0.113.7';
    private const HOST = 'cms-diizinkan.test';
    private const PROBE = '/__trusted-proxy-probe';
    private const LIMITED = '/__trusted-proxy-limited';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'trustedproxy.proxies' => [],
            'security.https_redirect_hosts' => [],
        ]);

        Route::get(self::PROBE, fn (Request $request) => [
            'ip' => $request->ip(),
            'secure' => $request->isSecure(),
            'host' => $request->getHost(),
            'url' => url('/'),
        ]);
    }

    private function probe(string $remoteAddr, array $headers = [], string $host = self::HOST)
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $remoteAddr])
            ->get('http://' . $host . self::PROBE, $headers);
    }

    /**
     * Rute uji terisolasi untuk L1/L2: limiter sendiri, 3 request per menit
     * per $request->ip(), lewat middleware throttle bawaan. Tidak bergantung
     * pada endpoint atau tabel fitur lain; kunci limiter hanya ditentukan
     * oleh TrustProxies.
     */
    private function limited(string $remoteAddr, string $forwardedFor)
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $remoteAddr])
            ->postJson('http://' . self::HOST . self::LIMITED, [], ['X-Forwarded-For' => $forwardedFor]);
    }

    private function registerLimitedRoute(): void
    {
        RateLimiter::for('trusted-proxy-test', fn (Request $request) => Limit::perMinute(3)->by('trusted-proxy-test:' . $request->ip()));

        Route::post(self::LIMITED, fn (Request $request) => ['ip' => $request->ip()])
            ->middleware('throttle:trusted-proxy-test');
    }

    /** P1 */
    public function test_forged_forwarded_for_from_direct_client_is_ignored(): void
    {
        $this->probe(self::CLIENT, ['X-Forwarded-For' => '192.0.2.99'])
            ->assertOk()
            ->assertJson(['ip' => self::CLIENT]);
    }

    /** P2 */
    public function test_forged_forwarded_proto_from_direct_client_is_ignored(): void
    {
        $this->probe(self::CLIENT, ['X-Forwarded-Proto' => 'https'])
            ->assertOk()
            ->assertJson(['secure' => false, 'url' => 'http://' . self::HOST]);
    }

    /** P3 */
    public function test_listed_proxy_headers_are_trusted(): void
    {
        config(['trustedproxy.proxies' => [self::PROXY]]);

        $this->probe(self::PROXY, ['X-Forwarded-For' => self::CLIENT, 'X-Forwarded-Proto' => 'https'])
            ->assertOk()
            ->assertJson(['ip' => self::CLIENT, 'secure' => true, 'url' => 'https://' . self::HOST]);
    }

    /** P4 */
    public function test_unlisted_peer_headers_are_ignored(): void
    {
        config(['trustedproxy.proxies' => [self::PROXY]]);

        $this->probe('198.51.100.20', ['X-Forwarded-For' => self::CLIENT, 'X-Forwarded-Proto' => 'https'])
            ->assertOk()
            ->assertJson(['ip' => '198.51.100.20', 'secure' => false]);
    }

    /** P5 */
    public function test_multi_hop_with_all_hops_listed_resolves_client(): void
    {
        config(['trustedproxy.proxies' => [self::PROXY, '198.51.100.11']]);

        $this->probe(self::PROXY, ['X-Forwarded-For' => self::CLIENT . ', 198.51.100.11'])
            ->assertJson(['ip' => self::CLIENT]);
    }

    /** P6: hop dalam yang tidak terdaftar menjadi IP klien (semua hop wajib terdaftar). */
    public function test_multi_hop_with_unlisted_inner_hop_stops_at_that_hop(): void
    {
        config(['trustedproxy.proxies' => [self::PROXY]]);

        $this->probe(self::PROXY, ['X-Forwarded-For' => self::CLIENT . ', 198.51.100.11'])
            ->assertJson(['ip' => '198.51.100.11']);
    }

    /** P7: XFP bernilai ganda memakai nilai pertama (dasar syarat proxy menimpa XFP). */
    public function test_multi_value_forwarded_proto_uses_first_value(): void
    {
        config(['trustedproxy.proxies' => [self::PROXY]]);

        $this->probe(self::PROXY, ['X-Forwarded-Proto' => 'http, https'])
            ->assertJson(['secure' => false]);
    }

    /** U1: X-Forwarded-Host tidak dipercaya, bahkan dari proxy terdaftar. */
    public function test_forwarded_host_is_ignored_even_from_listed_proxy(): void
    {
        config(['trustedproxy.proxies' => [self::PROXY]]);

        $this->probe(self::PROXY, ['X-Forwarded-Proto' => 'https', 'X-Forwarded-Host' => 'evil.test', 'X-Forwarded-Port' => '8443'])
            ->assertJson(['host' => self::HOST, 'url' => 'https://' . self::HOST]);
    }

    /** V1, V2 */
    public function test_ipv6_proxy_and_cidr_entries_match_their_family(): void
    {
        config(['trustedproxy.proxies' => ['10.0.0.0/8', '2001:db8::/64']]);

        $this->probe('2001:db8::10', ['X-Forwarded-For' => '2001:db8:ffff::7'])
            ->assertJson(['ip' => '2001:db8:ffff::7']);

        $this->probe('10.1.2.3', ['X-Forwarded-For' => self::CLIENT])
            ->assertJson(['ip' => self::CLIENT]);

        $this->probe('2001:db9::10', ['X-Forwarded-For' => self::CLIENT])
            ->assertJson(['ip' => '2001:db9::10']);
    }

    /** IPv4-mapped tidak didukung: peer ::ffff:a.b.c.d tidak cocok dengan entri IPv4. */
    public function test_ipv4_mapped_peer_is_not_trusted_by_ipv4_entry(): void
    {
        config(['trustedproxy.proxies' => [self::PROXY]]);

        $this->probe('::ffff:' . self::PROXY, ['X-Forwarded-For' => self::CLIENT])
            ->assertJson(['ip' => '::ffff:' . self::PROXY]);
    }

    /** C3: fallback trust-all Laravel Cloud tidak aktif karena daftar selalu array. */
    public function test_laravel_cloud_flag_does_not_enable_trust_all(): void
    {
        $previous = $_SERVER['LARAVEL_CLOUD'] ?? null;
        $_SERVER['LARAVEL_CLOUD'] = '1';

        try {
            $this->assertTrue(laravel_cloud());

            $this->probe(self::CLIENT, ['X-Forwarded-For' => '192.0.2.99'])
                ->assertJson(['ip' => self::CLIENT]);
        } finally {
            if ($previous === null) {
                unset($_SERVER['LARAVEL_CLOUD']);
            } else {
                $_SERVER['LARAVEL_CLOUD'] = $previous;
            }
        }
    }

    /** R1: proxy terdaftar, XFP http dialihkan sekali; XFP https tidak dialihkan (tanpa loop). */
    public function test_listed_proxy_http_is_redirected_once_and_https_passes(): void
    {
        config(['trustedproxy.proxies' => [self::PROXY], 'security.https_redirect_hosts' => [self::HOST]]);

        $this->probe(self::PROXY, ['X-Forwarded-Proto' => 'http'])
            ->assertStatus(302)
            ->assertHeader('Location', 'https://' . self::HOST . self::PROBE);

        $this->probe(self::PROXY, ['X-Forwarded-Proto' => 'https'])
            ->assertOk()
            ->assertJson(['secure' => true]);
    }

    /**
     * R2: kondisi redirect loop yang harus dicegah oleh urutan aktivasi A1-A4.
     * Peer tidak terdaftar dengan XFP https tetap dialihkan karena XFP diabaikan.
     */
    public function test_unlisted_peer_with_https_proto_is_redirected(): void
    {
        config(['security.https_redirect_hosts' => [self::HOST]]);

        $this->probe(self::PROXY, ['X-Forwarded-Proto' => 'https'])
            ->assertStatus(302)
            ->assertHeader('Location', 'https://' . self::HOST . self::PROBE);
    }

    /** R3 */
    public function test_unlisted_host_is_rejected_and_forwarded_host_is_ignored(): void
    {
        config(['trustedproxy.proxies' => [self::PROXY], 'security.https_redirect_hosts' => [self::HOST]]);

        $this->probe(self::PROXY, ['X-Forwarded-Proto' => 'http'], 'evil.test')->assertStatus(400);

        $this->probe(self::PROXY, ['X-Forwarded-Proto' => 'http', 'X-Forwarded-Host' => 'evil.test'])
            ->assertStatus(302)
            ->assertHeader('Location', 'https://' . self::HOST . self::PROBE);
    }

    /** S1: HSTS hanya bila HTTPS berasal dari proxy terdaftar. */
    public function test_hsts_requires_https_from_listed_proxy(): void
    {
        $this->app['env'] = 'production';
        config(['trustedproxy.proxies' => [self::PROXY]]);

        $this->probe(self::PROXY, ['X-Forwarded-Proto' => 'https'])
            ->assertHeader('Strict-Transport-Security');

        $this->probe(self::CLIENT, ['X-Forwarded-Proto' => 'https'])
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    /** L1: XFF yang berganti dari peer tidak terdaftar tidak memberi kuota baru. */
    public function test_rotating_forwarded_for_does_not_bypass_rate_limit(): void
    {
        $this->registerLimitedRoute();

        for ($i = 1; $i <= 3; $i++) {
            $this->limited(self::CLIENT, "192.0.2.{$i}")
                ->assertOk()
                ->assertJson(['ip' => self::CLIENT]);
        }

        $this->limited(self::CLIENT, '192.0.2.200')
            ->assertStatus(429)
            ->assertHeader('Retry-After');
    }

    /** L2: lewat proxy terdaftar, kuota dihitung per klien asli. */
    public function test_listed_proxy_rate_limit_is_per_client(): void
    {
        config(['trustedproxy.proxies' => [self::PROXY]]);
        $this->registerLimitedRoute();

        for ($i = 1; $i <= 3; $i++) {
            $this->limited(self::PROXY, self::CLIENT)
                ->assertOk()
                ->assertJson(['ip' => self::CLIENT]);
        }

        $this->limited(self::PROXY, self::CLIENT)
            ->assertStatus(429)
            ->assertHeader('Retry-After');

        // Klien lain di belakang proxy yang sama tidak ikut terblokir.
        $this->limited(self::PROXY, '203.0.113.8')
            ->assertOk()
            ->assertJson(['ip' => '203.0.113.8']);
    }

    /** C1, C2, K1, K3: config dibangun dari environment dan selalu berupa array. */
    public function test_config_file_builds_arrays_from_environment(): void
    {
        $cases = [
            [null, null, [], [], 'direct', 'default'],
            ['', '', [], [], 'direct', 'invalid'],
            ['   ', 'direct', [], [], 'direct', 'declared'],
            ['198.51.100.10,2001:DB8::/32', 'proxied', ['198.51.100.10', '2001:db8::/32'], [], 'proxied', 'declared'],
            ['198.51.100.10,*', 'proxied', [], [['position' => 2, 'reason' => 'FORBIDDEN_ALIAS']], 'proxied', 'declared'],
            ['::ffff:198.51.100.10', 'proxied', [], [['position' => 1, 'reason' => 'MAPPED_ADDRESS_UNSUPPORTED']], 'proxied', 'declared'],
            [null, 'null', [], [], 'direct', 'invalid'],
            [null, '(null)', [], [], 'direct', 'invalid'],
            [null, 'Direct', [], [], 'direct', 'invalid'],
        ];

        foreach ($cases as [$proxiesEnv, $modeEnv, $proxies, $rejected, $mode, $source]) {
            $config = $this->buildConfigWith(['TRUSTED_PROXIES' => $proxiesEnv, 'TRUSTED_PROXY_MODE' => $modeEnv]);
            $label = var_export([$proxiesEnv, $modeEnv], true);

            $this->assertSame(['proxies', 'rejected', 'mode', 'mode_source'], array_keys($config), $label);
            $this->assertSame($proxies, $config['proxies'], $label);
            $this->assertSame($rejected, $config['rejected'], $label);
            $this->assertSame($mode, $config['mode'], $label);
            $this->assertSame($source, $config['mode_source'], $label);

            // C2: bentuk yang ditulis config:cache (var_export) identik setelah dibaca ulang.
            $this->assertSame($config, eval('return ' . var_export($config, true) . ';'), $label);
        }
    }

    /**
     * Memuat config/trustedproxy.php dengan environment sementara (null = tidak
     * disetel) lalu memulihkan $_SERVER, $_ENV, dan getenv() seperti semula.
     */
    private function buildConfigWith(array $values): array
    {
        $saved = [];

        foreach ($values as $key => $value) {
            $env = getenv($key);
            $saved[$key] = [$_SERVER[$key] ?? null, $_ENV[$key] ?? null, $env === false ? null : $env, array_key_exists($key, $_SERVER), array_key_exists($key, $_ENV)];

            if ($value === null) {
                unset($_SERVER[$key], $_ENV[$key]);
                putenv($key);
            } else {
                $_SERVER[$key] = $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }

        try {
            return require config_path('trustedproxy.php');
        } finally {
            foreach ($saved as $key => [$server, $env, $getenv, $inServer, $inEnv]) {
                if ($inServer) {
                    $_SERVER[$key] = $server;
                } else {
                    unset($_SERVER[$key]);
                }

                if ($inEnv) {
                    $_ENV[$key] = $env;
                } else {
                    unset($_ENV[$key]);
                }

                putenv($getenv === null ? $key : "{$key}={$getenv}");
            }
        }
    }
}
