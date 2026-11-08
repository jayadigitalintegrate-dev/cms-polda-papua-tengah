<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ComplaintAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_update_complaint_status(): void
    {
        $operator = User::factory()->create([
            'role' => 'operator',
        ]);

        $complaint = Complaint::create([
            'nama' => 'Pelapor Test Operator',
            'ktp' => 'TEST-OPERATOR-001',
            'hp' => '080000000001',
            'email' => 'operator-complaint@example.test',
            'alamat' => 'Alamat pengujian',
            'jenis' => 'Pengujian',
            'isi' => 'Laporan untuk pengujian perubahan status.',
            'tiket' => 'TEST-OPERATOR-001',
            'status' => 'diterima',
        ]);

        $response = $this
            ->actingAs($operator)
            ->patch(
                route('complaints.update-status', $complaint),
                [
                    'status' => 'dikerjakan',
                    'catatan_admin' => 'Sedang ditindaklanjuti petugas.',
                ]
            );

        $response->assertRedirect(
            route('complaints.show', $complaint)
        );

        $complaint->refresh();

        $this->assertSame('dikerjakan', $complaint->status);
        $this->assertSame($operator->id, $complaint->processed_by);
        $this->assertNotNull($complaint->processed_at);

        $this->assertDatabaseHas('complaint_status_histories', [
            'complaint_id' => $complaint->id,
            'old_status' => 'diterima',
            'new_status' => 'dikerjakan',
            'actor_user_id' => $operator->id,
            'note' => 'Sedang ditindaklanjuti petugas.',
        ]);

        $this->assertSame(
            1,
            DB::table('complaint_status_histories')
                ->where('complaint_id', $complaint->id)
                ->count()
        );
    }
    public function test_operator_cannot_submit_invalid_status(): void
    {
        $operator = User::factory()->create([
            'role' => 'operator',
        ]);

        $complaint = Complaint::create([
            'nama' => 'Pelapor Validasi',
            'ktp' => 'TEST-VALIDATION-001',
            'hp' => '080000000002',
            'email' => 'validation@example.test',
            'alamat' => 'Alamat pengujian',
            'jenis' => 'Pengujian',
            'isi' => 'Laporan pengujian validasi.',
            'tiket' => 'TEST-VALIDATION-001',
            'status' => 'diterima',
        ]);

        $before = $complaint->status;

        $response = $this
            ->actingAs($operator)
            ->from(route('complaints.show', $complaint))
            ->patch(
                route('complaints.update-status', $complaint),
                [
                    'status' => 'status_palsu',
                    'catatan_admin' => 'Pengujian validasi.',
                ]
            );

        $response->assertRedirect(
            route('complaints.show', $complaint)
        );

        $response->assertSessionHasErrors('status');

        $this->assertSame(
            $before,
            $complaint->fresh()->status
        );

        $this->assertSame(
            0,
            DB::table('complaint_status_histories')
                ->where('complaint_id', $complaint->id)
                ->count()
        );
    }
    public function test_status_update_rolls_back_when_history_insert_fails(): void
    {
        $operator = User::factory()->create([
            'role' => 'operator',
        ]);

        $complaint = Complaint::create([
            'nama' => 'Pelapor Test Rollback',
            'ktp' => 'TEST-ROLLBACK-001',
            'hp' => '080000000003',
            'email' => 'rollback@example.test',
            'alamat' => 'Alamat pengujian',
            'jenis' => 'Pengujian',
            'isi' => 'Laporan untuk menguji rollback.',
            'tiket' => 'TEST-ROLLBACK-001',
            'status' => 'diterima',
        ]);

        $before = $complaint->fresh()->getAttributes();

        // Hanya pada SQLite :memory:.
        // Trigger sengaja menolak insert ke tabel riwayat.
        DB::unprepared("
            CREATE TRIGGER test_reject_complaint_history
            BEFORE INSERT ON complaint_status_histories
            BEGIN
                SELECT RAISE(ABORT, 'test history failure');
            END
        ");

        try {
            $this->withoutExceptionHandling();

            $this->actingAs($operator)->patch(
                route('complaints.update-status', $complaint),
                [
                    'status' => 'diproses',
                    'catatan_admin' => 'Pengujian rollback transaksi.',
                ]
            );

            $this->fail('Penyimpanan riwayat seharusnya gagal.');
        } catch (\Illuminate\Database\QueryException $exception) {
            $this->assertStringContainsString(
                'test history failure',
                $exception->getMessage()
            );
        } finally {
            DB::unprepared(
                'DROP TRIGGER IF EXISTS test_reject_complaint_history'
            );
        }

        $this->assertSame(
            $before,
            $complaint->fresh()->getAttributes()
        );

        $this->assertSame(
            0,
            DB::table('complaint_status_histories')
                ->where('complaint_id', $complaint->id)
                ->count()
        );
    }
    public function test_supervisi_can_view_but_cannot_update_complaint(): void
    {
        $supervisi = User::factory()->create([
            'role' => 'supervisi',
        ]);

        $complaint = Complaint::create([
            'nama' => 'Pelapor Test Supervisi',
            'ktp' => 'TEST-SUPERVISI-001',
            'hp' => '080000000004',
            'email' => 'supervisi@example.test',
            'alamat' => 'Alamat pengujian',
            'jenis' => 'Pengujian',
            'isi' => 'Laporan untuk menguji akses Supervisi.',
            'tiket' => 'TEST-SUPERVISI-001',
            'status' => 'diterima',
        ]);

        $this->actingAs($supervisi)
            ->get(route('complaints.show', $complaint))
            ->assertOk();

        $this->actingAs($supervisi)
            ->patch(
                route('complaints.update-status', $complaint),
                [
                    'status' => 'diproses',
                    'catatan_admin' => 'Perubahan tanpa izin.',
                ]
            )
            ->assertForbidden();

        $this->assertSame(
            'diterima',
            $complaint->fresh()->status
        );

        $this->assertSame(
            0,
            DB::table('complaint_status_histories')
                ->where('complaint_id', $complaint->id)
                ->count()
        );
    }
    public function test_superadmin_can_update_complaint_status(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $complaint = Complaint::create([
            'nama' => 'Pelapor Test Superadmin',
            'ktp' => 'TEST-SUPERADMIN-001',
            'hp' => '080000000005',
            'email' => 'superadmin@example.test',
            'alamat' => 'Alamat pengujian',
            'jenis' => 'Pengujian',
            'isi' => 'Laporan untuk pengujian Superadmin.',
            'tiket' => 'TEST-SUPERADMIN-001',
            'status' => 'diterima',
        ]);

        $this->actingAs($superadmin)
            ->patch(
                route('complaints.update-status', $complaint),
                [
                    'status' => 'diverifikasi',
                    'catatan_admin' => 'Laporan telah diverifikasi.',
                ]
            )
            ->assertRedirect(
                route('complaints.show', $complaint)
            );

        $complaint->refresh();

        $this->assertSame('diverifikasi', $complaint->status);
        $this->assertSame($superadmin->id, $complaint->processed_by);

        $this->assertDatabaseHas('complaint_status_histories', [
            'complaint_id' => $complaint->id,
            'old_status' => 'diterima',
            'new_status' => 'diverifikasi',
            'actor_user_id' => $superadmin->id,
            'note' => 'Laporan telah diverifikasi.',
        ]);

        $this->assertSame(
            1,
            DB::table('complaint_status_histories')
                ->where('complaint_id', $complaint->id)
                ->count()
        );
    }
    public function test_complaint_soft_delete_and_restore_preserves_history(): void
    {
        $operator = User::factory()->create([
            'role' => 'operator',
        ]);

        $complaint = Complaint::create([
            'nama' => 'Pelapor Test Soft Delete',
            'ktp' => 'TEST-SOFTDELETE-001',
            'hp' => '080000000006',
            'email' => 'softdelete@example.test',
            'alamat' => 'Alamat pengujian',
            'jenis' => 'Pengujian',
            'isi' => 'Laporan untuk pengujian soft delete.',
            'tiket' => 'TEST-SOFTDELETE-001',
            'status' => 'diterima',
        ]);

        $this->actingAs($operator)->patch(
            route('complaints.update-status', $complaint),
            [
                'status' => 'diproses',
                'catatan_admin' => 'Laporan sedang diproses.',
            ]
        )->assertRedirect();

        $complaint->refresh();

        $this->assertSame(1, DB::table('complaint_status_histories')
            ->where('complaint_id', $complaint->id)
            ->count());

        $complaint->delete();

        $this->assertSoftDeleted('complaints', [
            'id' => $complaint->id,
        ]);

        $this->assertNull(Complaint::find($complaint->id));

        $this->assertNotNull(
            Complaint::withTrashed()->find($complaint->id)
        );

        $this->assertSame(1, DB::table('complaint_status_histories')
            ->where('complaint_id', $complaint->id)
            ->count());

        $complaint->restore();

        $this->assertNotNull(Complaint::find($complaint->id));

        $this->assertNull(
            Complaint::find($complaint->id)->deleted_at
        );

        $this->assertSame(1, DB::table('complaint_status_histories')
            ->where('complaint_id', $complaint->id)
            ->count());
    }
    public function test_non_superadmin_cannot_bulk_delete_complaints(): void
    {
        $complaint = Complaint::create([
            'nama' => 'Pelapor Test Bulk Authorization',
            'ktp' => 'TEST-BULK-AUTH-001',
            'hp' => '080000000007',
            'email' => 'bulk-auth@example.test',
            'alamat' => 'Alamat pengujian',
            'jenis' => 'Pengujian',
            'isi' => 'Laporan untuk menguji otorisasi penghapusan.',
            'tiket' => 'TEST-BULK-AUTH-001',
            'status' => 'diterima',
        ]);

        foreach (['operator', 'supervisi', 'viewer'] as $role) {
            $user = User::factory()->create([
                'role' => $role,
            ]);

            $this->actingAs($user)
                ->post(route('complaints.bulk-delete'), [
                    'ids' => [$complaint->id],
                ])
                ->assertForbidden();

            $this->assertNotNull(
                Complaint::find($complaint->id)
            );

            $this->assertSame(
                0,
                DB::table('complaint_deletion_audits')->count()
            );
        }
    }
    public function test_superadmin_can_bulk_soft_delete_with_audit(): void
    {
        $this->withoutExceptionHandling();

        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $first = Complaint::create([
            'nama' => 'Pelapor Bulk Pertama',
            'ktp' => 'TEST-BULK-001',
            'hp' => '080000000011',
            'email' => 'bulk-first@example.test',
            'alamat' => 'Alamat pengujian',
            'jenis' => 'Pengujian',
            'isi' => 'Laporan pertama untuk penghapusan massal.',
            'tiket' => 'TEST-BULK-001',
            'status' => 'diterima',
        ]);

        $second = Complaint::create([
            'nama' => 'Pelapor Bulk Kedua',
            'ktp' => 'TEST-BULK-002',
            'hp' => '080000000012',
            'email' => 'bulk-second@example.test',
            'alamat' => 'Alamat pengujian',
            'jenis' => 'Pengujian',
            'isi' => 'Laporan kedua untuk penghapusan massal.',
            'tiket' => 'TEST-BULK-002',
            'status' => 'diterima',
        ]);


        $this->actingAs($superadmin)
            ->post(route('complaints.bulk-delete'), [
                'ids' => [$first->id, $second->id],
            ])
            ->assertRedirect(route('complaints.index'));

        foreach ([$first, $second] as $complaint) {
            $this->assertSoftDeleted('complaints', [
                'id' => $complaint->id,
            ]);

            $this->assertNull(Complaint::find($complaint->id));

            $this->assertNotNull(
                Complaint::withTrashed()->find($complaint->id)
            );

            $this->assertDatabaseHas('complaint_deletion_audits', [
                'complaint_id' => $complaint->id,
                'ticket' => $complaint->tiket,
                'action' => 'soft_delete',
                'actor_user_id' => $superadmin->id,
            ]);
        }

        $this->assertSame(
            2,
            DB::table('complaint_deletion_audits')->count()
        );

        $this->assertSame(
            2,
            Complaint::withTrashed()->count()
        );

        $this->assertSame(
            0,
            Complaint::count()
        );
    }
    public function test_viewer_cannot_update_complaint_status(): void
    {
        $user = User::factory()->create([
            'role' => 'viewer',
        ]);

        $complaint = Complaint::create([
            'nama' => 'Pelapor Pengujian',
            'ktp' => 'TEST-IDENTITY-001',
            'hp' => '080000000000',
            'email' => 'complaint-test@example.test',
            'alamat' => 'Alamat pengujian',
            'jenis' => 'Pengujian',
            'isi' => 'Laporan khusus pengujian otorisasi.',
            'tiket' => 'TEST-COMPLAINT-001',
            'status' => 'diterima',
        ]);

        $beforeStatus = $complaint->status;

        $beforeHistory = DB::table(
            'complaint_status_histories'
        )->count();

        $response = $this
            ->actingAs($user)
            ->patch(
                route('complaints.update-status', $complaint),
                [
                    'status' => 'diproses',
                    'catatan_admin' => 'Pengujian otorisasi.',
                ]
            );

        $response->assertForbidden();

        $this->assertSame(
            $beforeStatus,
            $complaint->fresh()->status
        );

        $this->assertSame(
            $beforeHistory,
            DB::table('complaint_status_histories')->count()
        );
    }
}
