<?php

namespace Tests\Feature\Security;

class SecurityHeadersTest extends SecurityTestCase
{
    public function test_hardening_headers_are_present_on_api_and_web_responses(): void
    {
        foreach (['/api/news', '/login'] as $uri) {
            $this->get($uri)
                ->assertHeader('X-Content-Type-Options', 'nosniff')
                ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
                ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
                ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        }
    }

    public function test_hsts_is_not_sent_outside_production(): void
    {
        $this->get('https://localhost/login')->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_hsts_is_not_sent_over_plain_http_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->get('http://localhost/login')->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_hsts_is_sent_over_https_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->get('https://localhost/login')->assertHeader('Strict-Transport-Security', 'max-age=31536000');
    }

    public function test_csp_is_not_enforced_yet(): void
    {
        // Content-Security-Policy belum diterapkan (follow-up, perlu audit asset).
        $this->get('/login')->assertHeaderMissing('Content-Security-Policy');
    }
}
