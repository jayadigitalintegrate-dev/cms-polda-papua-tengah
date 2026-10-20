<?php

namespace Tests\Feature;

use App\Models\FeedbackSubmission;
use Tests\Feature\Security\SecurityTestCase;

/**
 * Endpoint publik POST /api/feedback (Kritik & Saran).
 *
 * Memakai SecurityTestCase agar hanya berjalan di SQLite :memory:.
 */
class FeedbackSubmissionTest extends SecurityTestCase
{
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'category' => 'saran',
            'rating' => 4,
            'message' => 'Pelayanan SKCK sudah baik, mohon antrean dipercepat.',
            'name' => 'Warga Uji',
            'email' => 'warga.uji@example.com',
            'phone' => '0812 3456 7890',
            'consent' => true,
        ], $overrides);
    }

    public function test_valid_feedback_is_stored_and_acknowledged(): void
    {
        $response = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])
            ->postJson('/api/feedback', $this->validPayload());

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Masukan berhasil diterima.')
            ->assertJsonStructure(['message', 'data' => ['received_at']]);

        $this->assertDatabaseCount('feedback_submissions', 1);

        $feedback = FeedbackSubmission::firstOrFail();

        $this->assertSame('saran', $feedback->category);
        $this->assertSame(4, $feedback->rating);
        $this->assertSame('Warga Uji', $feedback->name);
        $this->assertSame('warga.uji@example.com', $feedback->email);
        $this->assertSame('0812 3456 7890', $feedback->phone);
        $this->assertSame('baru', $feedback->status);
        $this->assertSame(FeedbackSubmission::CONSENT_VERSION, $feedback->consent_version);
        $this->assertNotNull($feedback->consent_at);
        $this->assertSame(
            $feedback->created_at->toIso8601String(),
            $response->json('data.received_at')
        );
    }

    public function test_response_does_not_expose_internal_fields(): void
    {
        $this->postJson('/api/feedback', $this->validPayload())
            ->assertCreated()
            ->assertJsonMissingPath('data.id')
            ->assertJsonMissingPath('data.status')
            ->assertJsonMissingPath('data.ticket')
            ->assertJsonMissingPath('data.tiket');
    }

    public function test_raw_ip_is_never_stored_only_its_hmac(): void
    {
        $ip = '203.0.113.7';

        $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->postJson('/api/feedback', $this->validPayload())
            ->assertCreated();

        $feedback = FeedbackSubmission::firstOrFail();

        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $feedback->ip_hash);
        $this->assertSame(
            hash_hmac('sha256', $ip, (string) config('app.key')),
            $feedback->ip_hash
        );
        $this->assertStringNotContainsString($ip, json_encode($feedback->getAttributes()));
    }

    public function test_anonymous_feedback_without_identity_is_accepted(): void
    {
        $this->postJson('/api/feedback', [
            'category' => 'apresiasi',
            'rating' => 5,
            'message' => 'Terima kasih atas pelayanan yang cepat.',
            'consent' => true,
        ])->assertCreated();

        $this->assertDatabaseHas('feedback_submissions', [
            'category' => 'apresiasi',
            'name' => null,
            'email' => null,
            'phone' => null,
        ]);
    }

    public function test_empty_payload_is_rejected_without_storing(): void
    {
        $this->postJson('/api/feedback', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['category', 'rating', 'message', 'consent']);

        $this->assertDatabaseCount('feedback_submissions', 0);
    }

    public function test_invalid_values_are_rejected_per_field(): void
    {
        $this->postJson('/api/feedback', $this->validPayload([
            'category' => 'lainnya',
            'rating' => 6,
            'message' => 'pendek',
            'email' => 'bukan-email',
            'phone' => 'abc',
            'name' => str_repeat('a', 101),
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['category', 'rating', 'message', 'email', 'phone', 'name']);

        $this->postJson('/api/feedback', $this->validPayload([
            'rating' => 0,
            'message' => str_repeat('a', 5001),
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['rating', 'message']);

        $this->assertDatabaseCount('feedback_submissions', 0);
    }

    public function test_consent_must_be_accepted(): void
    {
        $this->postJson('/api/feedback', $this->validPayload(['consent' => false]))
            ->assertStatus(422)
            ->assertJsonPath('errors.consent.0', 'Persetujuan penggunaan data wajib dicentang.');

        $this->assertDatabaseCount('feedback_submissions', 0);
    }

    public function test_filled_honeypot_is_rejected_without_storing(): void
    {
        $this->postJson('/api/feedback', $this->validPayload(['website' => 'https://spam.example']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['website']);

        $this->assertDatabaseCount('feedback_submissions', 0);
    }

    public function test_empty_honeypot_is_accepted(): void
    {
        $this->postJson('/api/feedback', $this->validPayload(['website' => '']))
            ->assertCreated();

        $this->assertDatabaseCount('feedback_submissions', 1);
    }

    public function test_client_cannot_set_admin_handling_fields(): void
    {
        $this->postJson('/api/feedback', $this->validPayload([
            'status' => 'diarsipkan',
            'admin_note' => 'disisipkan klien',
            'reviewed_by' => 1,
            'reviewed_at' => '2026-01-01 00:00:00',
            'consent_version' => 'palsu',
            'ip_hash' => str_repeat('0', 64),
        ]))->assertCreated();

        $feedback = FeedbackSubmission::firstOrFail();

        $this->assertSame('baru', $feedback->status);
        $this->assertNull($feedback->admin_note);
        $this->assertNull($feedback->reviewed_by);
        $this->assertNull($feedback->reviewed_at);
        $this->assertSame(FeedbackSubmission::CONSENT_VERSION, $feedback->consent_version);
        $this->assertNotSame(str_repeat('0', 64), $feedback->ip_hash);
    }

    public function test_validation_messages_are_in_indonesian(): void
    {
        $this->postJson('/api/feedback', [])
            ->assertStatus(422)
            ->assertJsonPath('errors.category.0', 'Kategori wajib dipilih.')
            ->assertJsonPath('errors.message.0', 'Pesan wajib diisi.');
    }

    public function test_get_is_not_allowed_on_feedback_endpoint(): void
    {
        $this->getJson('/api/feedback')->assertStatus(405);
    }
}
