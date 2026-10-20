<?php

namespace Tests\Feature;

use App\Models\FeedbackSubmission;
use App\Models\User;
use Tests\Feature\Security\SecurityTestCase;

/**
 * Dashboard operator Kritik & Saran (/kritik-saran).
 *
 * Memakai SecurityTestCase agar hanya berjalan di SQLite :memory:.
 */
class FeedbackAdminTest extends SecurityTestCase
{
    private function feedback(array $overrides = []): FeedbackSubmission
    {
        return FeedbackSubmission::create(array_merge([
            'category' => 'saran',
            'rating' => 4,
            'message' => 'Mohon antrean SKCK dipercepat pada jam sibuk.',
            'name' => 'Warga Uji',
            'email' => 'warga.uji@example.com',
            'phone' => '0812 3456 7890',
            'consent_at' => now(),
            'consent_version' => FeedbackSubmission::CONSENT_VERSION,
            'ip_hash' => str_repeat('a', 64),
            'status' => 'baru',
        ], $overrides));
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $feedback = $this->feedback();

        $this->get('/kritik-saran')->assertRedirect('/login');
        $this->get('/kritik-saran/' . $feedback->id)->assertRedirect('/login');
        $this->patch('/kritik-saran/' . $feedback->id, ['status' => 'ditinjau'])->assertRedirect('/login');

        $this->assertSame('baru', $feedback->fresh()->status);
    }

    public function test_account_with_unrecognised_role_is_forbidden(): void
    {
        $feedback = $this->feedback();
        $user = $this->user('tamu');

        $this->actingAs($user)->get('/kritik-saran')->assertForbidden();
        $this->actingAs($user)->get('/kritik-saran/' . $feedback->id)->assertForbidden();
        $this->actingAs($user)->patch('/kritik-saran/' . $feedback->id, ['status' => 'ditinjau'])->assertForbidden();

        $this->assertSame('baru', $feedback->fresh()->status);
        $this->assertNull($feedback->fresh()->reviewed_by);
    }

    public function test_cms_roles_can_open_the_dashboard(): void
    {
        foreach (['superadmin', 'operator', 'supervisi'] as $role) {
            $this->actingAs($this->user($role))->get('/kritik-saran')->assertOk();
        }
    }

    /**
     * Pembatasan sementara: hak ubah supervisi belum disahkan (F-07), sehingga
     * supervisi hanya membaca. Ubah tes ini bila Project Owner memutuskan lain.
     */
    public function test_supervisi_is_read_only_until_f07_is_decided(): void
    {
        $feedback = $this->feedback(['admin_note' => 'Catatan operator.']);
        $supervisi = $this->user('supervisi');

        $this->actingAs($supervisi)->get('/kritik-saran/' . $feedback->id)
            ->assertOk()
            ->assertSee('hanya memiliki akses baca')
            ->assertSee('Catatan operator.')
            ->assertDontSee('Simpan Perubahan');

        $this->actingAs($supervisi)->patch('/kritik-saran/' . $feedback->id, ['status' => 'diarsipkan'])
            ->assertForbidden();

        $this->assertSame('baru', $feedback->fresh()->status);
        $this->assertNull($feedback->fresh()->reviewed_by);
    }

    public function test_superadmin_can_update_status(): void
    {
        $feedback = $this->feedback();

        $this->actingAs($this->user('superadmin'))
            ->patch('/kritik-saran/' . $feedback->id, ['status' => 'ditinjau'])
            ->assertSessionHasNoErrors();

        $this->assertSame('ditinjau', $feedback->fresh()->status);
    }

    public function test_list_shows_date_category_rating_summary_and_status_but_no_identity(): void
    {
        $this->feedback(['message' => str_repeat('Pelayanan ', 30) . 'EKORPESAN']);

        $response = $this->actingAs($this->user('operator'))->get('/kritik-saran');

        $response->assertOk()
            ->assertSee('Saran')
            ->assertSee('4/5')
            ->assertSee('Pelayanan Pelayanan')
            ->assertDontSee('EKORPESAN')
            ->assertSee('Baru')
            ->assertDontSee('warga.uji@example.com')
            ->assertDontSee('0812 3456 7890');
    }

    public function test_category_status_and_search_filters(): void
    {
        $this->feedback(['category' => 'kritik', 'message' => 'Kritik tentang parkir kantor.']);
        $this->feedback(['category' => 'apresiasi', 'status' => 'diarsipkan', 'message' => 'Apresiasi petugas piket malam.']);
        $this->feedback(['category' => 'saran', 'message' => 'Saran ruang tunggu ber-AC.', 'email' => 'cari.saya@example.com']);

        $operator = $this->user('operator');

        $this->actingAs($operator)->get('/kritik-saran?category=kritik')
            ->assertSee('Kritik tentang parkir kantor.')
            ->assertDontSee('Apresiasi petugas piket malam.')
            ->assertDontSee('Saran ruang tunggu ber-AC.');

        $this->actingAs($operator)->get('/kritik-saran?status=diarsipkan')
            ->assertSee('Apresiasi petugas piket malam.')
            ->assertDontSee('Kritik tentang parkir kantor.');

        $this->actingAs($operator)->get('/kritik-saran?search=cari.saya')
            ->assertSee('Saran ruang tunggu ber-AC.')
            ->assertDontSee('Kritik tentang parkir kantor.');

        // Nilai filter di luar daftar diabaikan, bukan dipakai ke query.
        $this->actingAs($operator)->get('/kritik-saran?status=dihapus&category=xx')
            ->assertOk()
            ->assertSee('Kritik tentang parkir kantor.')
            ->assertSee('Apresiasi petugas piket malam.');
    }

    public function test_list_is_paginated_by_fifteen(): void
    {
        for ($i = 1; $i <= 16; $i++) {
            $this->feedback(['message' => "Masukan nomor urut {$i} untuk uji."]);
        }

        $operator = $this->user('operator');

        $this->actingAs($operator)->get('/kritik-saran')
            ->assertSee('Total: 16 masukan')
            ->assertSee('page=2', false);

        $this->actingAs($operator)->get('/kritik-saran?page=2')->assertOk();
    }

    public function test_detail_shows_message_and_optional_identity_escaped(): void
    {
        $feedback = $this->feedback([
            'message' => '<script>alert("x")</script> pelayanan perlu dibenahi',
            'name' => '<b>Nama</b>',
        ]);

        $this->actingAs($this->user('operator'))->get('/kritik-saran/' . $feedback->id)
            ->assertOk()
            ->assertSee('pelayanan perlu dibenahi')
            ->assertSee('warga.uji@example.com')
            ->assertSee('0812 3456 7890')
            ->assertDontSee('<script>alert("x")</script>', false)
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<b>Nama</b>', false);
    }

    public function test_detail_of_anonymous_feedback_says_not_filled(): void
    {
        $feedback = $this->feedback(['name' => null, 'email' => null, 'phone' => null]);

        $this->actingAs($this->user('operator'))->get('/kritik-saran/' . $feedback->id)
            ->assertOk()
            ->assertSee('Tidak diisi');
    }

    public function test_operator_updates_status_and_note_and_is_recorded_as_reviewer(): void
    {
        $feedback = $this->feedback();
        $operator = $this->user('operator');

        $this->actingAs($operator)
            ->patch('/kritik-saran/' . $feedback->id, [
                'status' => 'ditindaklanjuti',
                'admin_note' => 'Diteruskan ke Satuan Intelkam.',
            ])
            ->assertRedirect(route('feedback.show', $feedback))
            ->assertSessionHas('success');

        $fresh = $feedback->fresh();
        $this->assertSame('ditindaklanjuti', $fresh->status);
        $this->assertSame('Diteruskan ke Satuan Intelkam.', $fresh->admin_note);
        $this->assertSame($operator->id, $fresh->reviewed_by);
        $this->assertNotNull($fresh->reviewed_at);

        $this->actingAs($operator)->get('/kritik-saran/' . $feedback->id)
            ->assertSee('Diteruskan ke Satuan Intelkam.')
            ->assertSee($operator->name);
    }

    public function test_all_four_statuses_are_accepted(): void
    {
        $feedback = $this->feedback();
        $operator = $this->user('operator');

        foreach (['ditinjau', 'ditindaklanjuti', 'diarsipkan', 'baru'] as $status) {
            $this->actingAs($operator)->patch('/kritik-saran/' . $feedback->id, ['status' => $status])
                ->assertSessionHasNoErrors();
            $this->assertSame($status, $feedback->fresh()->status);
        }
    }

    public function test_saving_without_changes_keeps_the_previous_reviewer(): void
    {
        $first = $this->user('operator');
        $second = $this->user('superadmin');
        $feedback = $this->feedback();

        $this->actingAs($first)->patch('/kritik-saran/' . $feedback->id, ['status' => 'ditinjau', 'admin_note' => 'Catatan.']);
        $this->actingAs($second)->patch('/kritik-saran/' . $feedback->id, ['status' => 'ditinjau', 'admin_note' => 'Catatan.']);

        $this->assertSame($first->id, $feedback->fresh()->reviewed_by);
    }

    public function test_invalid_status_and_long_note_are_rejected(): void
    {
        $feedback = $this->feedback();
        $operator = $this->user('operator');

        $this->actingAs($operator)->patch('/kritik-saran/' . $feedback->id, ['status' => 'dihapus'])
            ->assertSessionHasErrors('status');

        $this->actingAs($operator)->patch('/kritik-saran/' . $feedback->id, [
            'status' => 'ditinjau',
            'admin_note' => str_repeat('x', 5001),
        ])->assertSessionHasErrors('admin_note');

        $this->assertSame('baru', $feedback->fresh()->status);
        $this->assertNull($feedback->fresh()->reviewed_by);
    }

    public function test_review_fields_cannot_be_set_through_the_form(): void
    {
        $feedback = $this->feedback();
        $operator = $this->user('operator');
        $other = $this->user('superadmin');

        $this->actingAs($operator)->patch('/kritik-saran/' . $feedback->id, [
            'status' => 'ditinjau',
            'reviewed_by' => $other->id,
            'reviewed_at' => '2000-01-01 00:00:00',
            'message' => 'diubah',
        ]);

        $fresh = $feedback->fresh();
        $this->assertSame($operator->id, $fresh->reviewed_by);
        $this->assertNotSame('2000', $fresh->reviewed_at->format('Y'));
        $this->assertSame('Mohon antrean SKCK dipercepat pada jam sibuk.', $fresh->message);
    }

    public function test_no_delete_route_and_no_public_read_api(): void
    {
        $feedback = $this->feedback();

        $this->actingAs($this->user('superadmin'))->delete('/kritik-saran/' . $feedback->id)
            ->assertStatus(405);
        $this->assertNotNull($feedback->fresh());

        $this->getJson('/api/feedback')->assertStatus(405);
        $this->getJson('/api/feedback/' . $feedback->id)->assertNotFound();
    }
}
