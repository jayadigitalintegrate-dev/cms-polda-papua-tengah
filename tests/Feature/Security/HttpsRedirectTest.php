<?php

namespace Tests\Feature\Security;

class HttpsRedirectTest extends SecurityTestCase
{
    private const ALLOWED_HOST = 'cms-diizinkan.test';

    protected function setUp(): void
    {
        parent::setUp();

        config(['security.https_redirect_hosts' => [self::ALLOWED_HOST]]);
    }

    public function test_proxied_http_request_for_allowed_host_is_redirected_to_https(): void
    {
        $response = $this->get('http://' . self::ALLOWED_HOST . '/login', ['X-Forwarded-Proto' => 'http']);

        $response->assertStatus(302)
            ->assertHeader('Location', 'https://' . self::ALLOWED_HOST . '/login');

        $this->assertSame([], $response->headers->getCookies());
    }

    public function test_proxied_https_request_is_not_redirected(): void
    {
        $this->get('http://' . self::ALLOWED_HOST . '/login', ['X-Forwarded-Proto' => 'https'])
            ->assertOk();
    }

    public function test_direct_http_request_without_proxy_header_is_not_redirected(): void
    {
        $this->get('http://127.0.0.1:8000/login')->assertOk();
    }

    public function test_proxied_http_request_for_unlisted_host_is_rejected(): void
    {
        $this->get('http://host-tidak-terdaftar.test/login', ['X-Forwarded-Proto' => 'http'])
            ->assertStatus(400);
    }

    public function test_empty_host_list_disables_the_redirect(): void
    {
        config(['security.https_redirect_hosts' => []]);

        $this->get('http://' . self::ALLOWED_HOST . '/login', ['X-Forwarded-Proto' => 'http'])
            ->assertOk();
    }
}
