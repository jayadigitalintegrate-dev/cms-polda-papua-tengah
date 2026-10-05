<?php

namespace Tests\Feature\Security;

class CorsTest extends SecurityTestCase
{
    public function test_configured_origins_are_allowed(): void
    {
        foreach (['http://localhost:5173', 'https://jayadigitalintegrate-dev.github.io'] as $origin) {
            $this->getJson('/api/news', ['Origin' => $origin])
                ->assertOk()
                ->assertHeader('Access-Control-Allow-Origin', $origin);
        }
    }

    public function test_unknown_origin_is_not_allowed(): void
    {
        $this->getJson('/api/news', ['Origin' => 'https://evil.example'])
            ->assertOk()
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_wildcard_origin_is_not_configured(): void
    {
        $this->assertNotContains('*', config('cors.allowed_origins'));
        $this->assertSame(['GET', 'POST'], config('cors.allowed_methods'));
    }

    public function test_preflight_for_website_post_is_allowed(): void
    {
        $response = $this->call('OPTIONS', '/api/complaints', [], [], [], [
            'HTTP_ORIGIN' => 'http://localhost:5173',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'content-type,accept',
        ]);

        $response->assertNoContent();
        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
        $this->assertStringContainsString('POST', (string) $response->headers->get('Access-Control-Allow-Methods'));
    }

    public function test_preflight_for_disallowed_method_is_rejected(): void
    {
        $response = $this->call('OPTIONS', '/api/complaints', [], [], [], [
            'HTTP_ORIGIN' => 'http://localhost:5173',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'DELETE',
        ]);

        $this->assertStringNotContainsString('DELETE', (string) $response->headers->get('Access-Control-Allow-Methods'));
    }
}
