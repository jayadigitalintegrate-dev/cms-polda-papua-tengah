<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ComplaintBulkDeleteSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function complaint(string $ticket): Complaint
    {
        return Complaint::create([
            'nama' => 'Pelapor Security Test',
            'ktp' => $ticket,
            'hp' => '080000000000',
            'email' => 'security@example.test',
            'alamat' => 'Alamat pengujian',
            'jenis' => 'Pengujian',
            'isi' => 'Laporan pengujian keamanan bulk delete.',
            'tiket' => $ticket,
            'status' => 'diterima',
        ]);
    }

    public static function invalidSelections(): array
    {
        // Symbolic IDs are resolved only after the guarded in-memory setup.
        return [
            'missing IDs' => [null, 'ids'],
            'empty IDs' => [[], 'ids'],
            'non-array IDs' => ['invalid', 'ids'],
            'non-integer ID' => [['valid', 'invalid'], 'ids.1'],
            'zero ID' => [['valid', 0], 'ids.1'],
            'negative ID' => [['valid', -1], 'ids.1'],
            'duplicate IDs' => [['valid', 'valid'], 'ids.0'],
            'more than 15 existing IDs' => ['sixteen', 'ids'],
            'one missing complaint among valid IDs' => [['valid', 'missing'], 'ids'],
            'one deleted complaint among valid IDs' => [['valid', 'deleted'], 'ids'],
        ];
    }

    #[DataProvider('invalidSelections')]
    public function test_invalid_selection_rejects_entire_batch(mixed $selection, string $error): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $valid = $this->complaint('SEC-VALID');
        $deleted = $this->complaint('SEC-DELETED');
        $deleted->delete();

        if ($selection === 'sixteen') {
            $selection = [$valid->id];
            for ($i = 1; $i < 16; $i++) {
                $selection[] = $this->complaint('SEC-LIMIT-'.$i)->id;
            }
        } elseif (is_array($selection)) {
            $selection = array_map(fn ($id) => match ($id) {
                'valid' => $valid->id,
                'deleted' => $deleted->id,
                'missing' => $deleted->id + 1000,
                default => $id,
            }, $selection);
        }

        $before = DB::table('complaints')->orderBy('id')->get()->toArray();
        $payload = [];
        if ($selection !== null) {
            $payload['ids'] = $selection;
        }

        $this->actingAs($admin)->from(route('complaints.index'))
            ->post(route('complaints.bulk-delete'), $payload)
            ->assertRedirect(route('complaints.index'))
            ->assertSessionHasErrors($error);

        $this->assertEquals($before, DB::table('complaints')->orderBy('id')->get()->toArray());
        $this->assertDatabaseCount('complaint_deletion_audits', 0);
    }

    public function test_bulk_delete_needs_only_selected_ids_and_preserves_technical_audit(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $complaint = $this->complaint('SEC-TECHNICAL-AUDIT');
        $payload = ['ids' => [$complaint->id]];

        $this->actingAs($admin)->from(route('complaints.index'))
            ->post(route('complaints.bulk-delete'), $payload)
            ->assertRedirect(route('complaints.index'))
            ->assertSessionHasNoErrors();

        $this->assertSoftDeleted('complaints', ['id' => $complaint->id]);
        $this->assertDatabaseCount('complaints', 1);
        $this->assertDatabaseCount('complaint_deletion_audits', 1);
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('complaint_deletion_audits', 'reason'));
        $audit = \App\Models\ComplaintDeletionAudit::firstOrFail();
        $this->assertNotContains('reason', $audit->getFillable());
        $this->assertSame($complaint->id, $audit->complaint_id);
        $this->assertSame($complaint->tiket, $audit->ticket);
        $this->assertSame('soft_delete', $audit->action);
        $this->assertSame($admin->id, $audit->actor_user_id);
        $this->assertNotNull($audit->created_at);
    }

    public function test_second_audit_insert_failure_rolls_back_entire_batch(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $first = $this->complaint('SEC-ROLLBACK-1');
        $second = $this->complaint('SEC-ROLLBACK-2');
        $before = DB::table('complaints')->orderBy('id')->get()->toArray();

        // Fail only after one audit has been inserted, proving batch atomicity.
        DB::unprepared("CREATE TRIGGER test_reject_second_deletion_audit
            BEFORE INSERT ON complaint_deletion_audits
            WHEN (SELECT COUNT(*) FROM complaint_deletion_audits) = 1
            BEGIN
                SELECT RAISE(ABORT, 'test second audit failure');
            END");

        try {
            $this->withoutExceptionHandling();
            $this->actingAs($admin)->post(route('complaints.bulk-delete'), [
                'ids' => [$first->id, $second->id],
            ]);
            $this->fail('Insert audit kedua seharusnya gagal.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('test second audit failure', $exception->getMessage());
        } finally {
            DB::unprepared('DROP TRIGGER IF EXISTS test_reject_second_deletion_audit');
        }

        $this->assertEquals($before, DB::table('complaints')->orderBy('id')->get()->toArray());
        $this->assertDatabaseCount('complaint_deletion_audits', 0);
        $this->assertSame(2, Complaint::count());
    }

    public function test_bulk_soft_delete_preserves_history_and_physical_rows(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $first = $this->complaint('SEC-HISTORY-1');
        $second = $this->complaint('SEC-HISTORY-2');
        $unselected = $this->complaint('SEC-UNSELECTED');

        foreach ([$first, $second] as $complaint) {
            $this->actingAs($admin)->patch(route('complaints.update-status', $complaint), [
                'status' => 'diproses',
                'catatan_admin' => 'Riwayat yang wajib dipertahankan.',
            ])->assertRedirect();
        }
        $history = DB::table('complaint_status_histories')->orderBy('id')->get()->toArray();
        $this->assertCount(2, $history);
        $untouched = $unselected->fresh()->getAttributes();

        $this->actingAs($admin)->post(route('complaints.bulk-delete'), [
            'ids' => [$first->id, $second->id],
        ])->assertRedirect(route('complaints.index'));

        $this->assertEquals($history, DB::table('complaint_status_histories')->orderBy('id')->get()->toArray());
        $this->assertDatabaseCount('complaints', 3);
        $this->assertSame($untouched, $unselected->fresh()->getAttributes());
        $this->assertSame(1, Complaint::count());
        $this->assertDatabaseCount('complaint_deletion_audits', 2);

        foreach ([$first, $second] as $complaint) {
            $this->assertSoftDeleted('complaints', ['id' => $complaint->id]);
            $this->assertNull(Complaint::find($complaint->id));
            $this->assertNotNull(Complaint::withTrashed()->find($complaint->id));
            $audits = DB::table('complaint_deletion_audits')->where('complaint_id', $complaint->id)->get();
            $this->assertCount(1, $audits);
            $this->assertSame($complaint->tiket, $audits[0]->ticket);
            $this->assertSame('soft_delete', $audits[0]->action);
            $this->assertSame($admin->id, $audits[0]->actor_user_id);
            $this->assertNotNull($audits[0]->created_at);
        }
    }
}
