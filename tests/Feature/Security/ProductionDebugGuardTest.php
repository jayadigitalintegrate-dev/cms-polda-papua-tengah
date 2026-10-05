<?php

namespace Tests\Feature\Security;

use RuntimeException;

class ProductionDebugGuardTest extends SecurityTestCase
{
    public function test_production_forces_debug_off_even_when_app_debug_is_true(): void
    {
        $this->assertFalse($this->debugFor('production', 'true'));
    }

    public function test_local_keeps_app_debug_value(): void
    {
        $this->assertTrue($this->debugFor('local', 'true'));
        $this->assertFalse($this->debugFor('local', 'false'));
    }

    public function test_api_errors_do_not_leak_internals_when_debug_is_off(): void
    {
        config(['app.debug' => false]);

        $this->getJson('/api/does-not-exist')
            ->assertNotFound()
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace')
            ->assertJsonMissingPath('file');

        $response = $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)->render(
            \Illuminate\Http\Request::create('/api/news'),
            new RuntimeException('SQLSTATE[42S02] secret table at D:\\path\\file.php')
        );

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame(['message' => 'Server Error'], json_decode($response->getContent(), true));
    }

    /**
     * Evaluasi ulang config/app.php dengan APP_ENV/APP_DEBUG tertentu,
     * lalu kembalikan environment seperti semula.
     */
    private function debugFor(string $env, string $debug): bool
    {
        $keys = ['APP_ENV' => $env, 'APP_DEBUG' => $debug];
        $backup = [];

        foreach ($keys as $key => $value) {
            $backup[$key] = [$_ENV[$key] ?? null, $_SERVER[$key] ?? null, getenv($key)];
            $_ENV[$key] = $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
        }

        try {
            $config = require config_path('app.php');

            return (bool) $config['debug'];
        } finally {
            foreach ($backup as $key => [$envValue, $serverValue, $putValue]) {
                if ($envValue === null) {
                    unset($_ENV[$key]);
                } else {
                    $_ENV[$key] = $envValue;
                }

                if ($serverValue === null) {
                    unset($_SERVER[$key]);
                } else {
                    $_SERVER[$key] = $serverValue;
                }

                putenv($putValue === false ? $key : "{$key}={$putValue}");
            }
        }
    }
}
