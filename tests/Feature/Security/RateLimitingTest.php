<?php

namespace Tests\Feature\Security;

class RateLimitingTest extends SecurityTestCase
{
    public function test_public_read_endpoints_expose_the_public_read_limit(): void
    {
        foreach (['news', 'announcement-popup', 'contact', 'heroes', 'announcements', 'ppid-documents', 'officials', 'police-stations'] as $endpoint) {
            $this->getJson("/api/{$endpoint}")
                ->assertOk()
                ->assertHeader('X-RateLimit-Limit', '300');
        }
    }

    public function test_complaints_endpoint_is_throttled_per_ip_after_five_requests(): void
    {
        $this->assertWriteEndpointThrottled('/api/complaints');
    }

    public function test_ppid_requests_endpoint_is_throttled_per_ip_after_five_requests(): void
    {
        $this->assertWriteEndpointThrottled('/api/ppid-requests');
    }

    public function test_forgot_password_is_throttled_after_six_requests(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $this->post('/forgot-password', [])->assertStatus(302);
        }

        $this->post('/forgot-password', [])->assertStatus(429);
    }

    private function assertWriteEndpointThrottled(string $uri): void
    {
        $attacker = ['REMOTE_ADDR' => '203.0.113.7'];

        // Payload kosong: validasi gagal (422), tidak ada data tersimpan.
        for ($i = 1; $i <= 5; $i++) {
            $this->withServerVariables($attacker)->postJson($uri, [])->assertStatus(422);
        }

        $this->withServerVariables($attacker)->postJson($uri, [])
            ->assertStatus(429)
            ->assertHeader('Retry-After');

        // IP lain tidak ikut terblokir.
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.9'])->postJson($uri, [])->assertStatus(422);
    }
}
