<?php

namespace Tests\Feature\Security;

use App\Console\Commands\SecurityPreflight;

class SecurityPreflightCommandTest extends SecurityTestCase
{
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
