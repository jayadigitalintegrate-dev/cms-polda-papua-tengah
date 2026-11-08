<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplaintBulkDeleteHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_ids_only_require_authenticated_session_and_valid_csrf(): void
    {
        // Enforce CSRF despite Laravel's automatic unit-test bypass.
        // The existing SQLite :memory: safety guards remain active.
        $this->app->bind(PreventRequestForgery::class, fn ($app) => new class($app, $app['encrypter']) extends PreventRequestForgery
        {
            protected function runningUnitTests()
            {
                return false;
            }
        });

        $admin = User::factory()->create(['role' => 'superadmin']);
        $complaint = Complaint::create([
            'nama' => 'Synthetic HTTP Reporter', 'ktp' => 'TEST-HTTP-095',
            'hp' => '080000000000', 'email' => 'http095@example.test',
            'alamat' => 'Synthetic address', 'jenis' => 'Pengujian', 'isi' => 'Synthetic HTTP security test.',
            'tiket' => 'TEST-HTTP-095', 'status' => 'diterima',
        ]);
        $payload = ['ids' => [$complaint->id]];
        $token = str_repeat('p', 40);

        $this->get(route('complaints.index'))->assertRedirect(route('login'));
        $this->actingAs($admin)->withSession(['_token' => $token])
            ->post(route('complaints.bulk-delete'), $payload)->assertStatus(419);
        $this->withSession(['_token' => $token])
            ->post(route('complaints.bulk-delete'), $payload + ['_token' => 'invalid'])->assertStatus(419);
        $this->assertNotNull(Complaint::find($complaint->id));
        $this->assertDatabaseCount('complaint_deletion_audits', 0);

        $this->withSession(['_token' => $token])
            ->post(route('complaints.bulk-delete'), $payload + ['_token' => $token])
            ->assertRedirect(route('complaints.index'));
        $this->assertSoftDeleted('complaints', ['id' => $complaint->id]);
        $this->assertDatabaseCount('complaints', 1);
        $this->assertDatabaseHas('complaint_deletion_audits', [
            'complaint_id' => $complaint->id, 'ticket' => $complaint->tiket,
            'action' => 'soft_delete', 'actor_user_id' => $admin->id,
        ]);
    }
}
