<?php

namespace Tests\Feature\Security;

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
}
