<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ComplaintPreMigrationCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return [
            'nama' => 'Pelapor Compatibility', 'ktp' => 'COMPAT-001',
            'hp' => '080000000000', 'email' => 'compat@example.test',
            'alamat' => 'Alamat tes', 'jenis' => 'Pengujian', 'isi' => 'Laporan tes kompatibilitas.',
        ];
    }

    public function test_public_submission_and_admin_detail_work_before_feature_migrations(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        // Simulate a legacy schema only within the guarded SQLite :memory: connection.
        Schema::table('complaints', fn (Blueprint $table) => $table->renameColumn('deleted_at', 'pending_deleted_at'));
        Schema::rename('complaint_status_histories', 'pending_complaint_status_histories');
        Schema::rename('complaint_deletion_audits', 'pending_complaint_deletion_audits');

        try {
            $response = $this->postJson('/api/complaints', $this->payload())
                ->assertCreated()->assertJsonPath('data.status', 'diterima');
            $id = $response->json('data.id');
            $this->assertDatabaseHas('complaints', ['id' => $id, 'tiket' => $response->json('data.tiket')]);

            $this->actingAs($admin)->get(route('complaints.show', $id))
                ->assertOk()->assertSee('Pelapor Compatibility')
                ->assertSee('Fitur pembaruan status belum diaktifkan.')
                ->assertDontSee('name="catatan_admin"', false);
            $this->get(route('complaints.index'))->assertOk()
                ->assertSee($response->json('data.tiket'))
                ->assertDontSee('id="complaint-bulk-delete"', false);
            $this->patch(route('complaints.update-status', $id), ['status' => 'diproses', 'catatan_admin' => 'Tes'])
                ->assertStatus(503);
            $this->post(route('complaints.bulk-delete'), ['ids' => [$id]])
                ->assertStatus(503);
            $this->assertDatabaseHas('complaints', ['id' => $id, 'status' => 'diterima', 'pending_deleted_at' => null]);
            $this->assertDatabaseCount('pending_complaint_status_histories', 0);
            $this->assertDatabaseCount('pending_complaint_deletion_audits', 0);

            $this->actingAs(User::factory()->create(['role' => 'viewer']))
                ->get(route('complaints.show', $id))->assertForbidden();
        } finally {
            Schema::rename('pending_complaint_deletion_audits', 'complaint_deletion_audits');
            Schema::rename('pending_complaint_status_histories', 'complaint_status_histories');
            Schema::table('complaints', fn (Blueprint $table) => $table->renameColumn('pending_deleted_at', 'deleted_at'));
        }
    }

    public function test_route_binding_rechecks_schema_and_excludes_deleted_reports_after_migration(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $complaint = Complaint::create($this->payload() + ['tiket' => 'COMPAT-ROUTE', 'status' => 'diterima']);
        Schema::table('complaints', fn (Blueprint $table) => $table->renameColumn('deleted_at', 'pending_deleted_at'));
        try {
            $this->actingAs($admin)->get(route('complaints.show', $complaint))->assertOk();
        } finally {
            Schema::table('complaints', fn (Blueprint $table) => $table->renameColumn('pending_deleted_at', 'deleted_at'));
        }

        $this->get(route('complaints.show', $complaint))->assertOk();
        $this->post(route('complaints.bulk-delete'), ['ids' => [$complaint->id]])
            ->assertRedirect(route('complaints.index'));
        $this->get(route('complaints.show', $complaint))->assertNotFound();
        $this->get(route('complaints.index'))->assertOk()->assertDontSee('COMPAT-ROUTE');
        $this->patch(route('complaints.update-status', $complaint), ['status' => 'diproses', 'catatan_admin' => 'Tes'])
            ->assertNotFound();
        $this->assertNull(Complaint::find($complaint->id));
        $this->assertDatabaseCount('complaints', 1);
        $this->assertDatabaseCount('complaint_deletion_audits', 1);
        $this->assertSame(0, DB::table('complaint_status_histories')->count());
    }

    public function test_public_submission_still_works_with_migrated_schema(): void
    {
        $this->postJson('/api/complaints', $this->payload())->assertCreated()
            ->assertJsonPath('data.status', 'diterima');
        $this->assertSame(1, Complaint::count());
        $this->assertDatabaseCount('complaint_deletion_audits', 0);
    }

    public static function ticketCollisionSchemas(): array
    {
        return [
            'existing ticket before migration' => [true, false],
            'existing ticket after migration' => [false, false],
            'soft-deleted ticket after migration' => [false, true],
        ];
    }

    #[DataProvider('ticketCollisionSchemas')]
    public function test_public_submission_retries_tickets_reserved_by_unique_index(bool $legacy, bool $deleted): void
    {
        if ($legacy) {
            Schema::table('complaints', fn (Blueprint $table) => $table->renameColumn('deleted_at', 'pending_deleted_at'));
        }

        $reservedTicket = null;
        $checks = 0;
        $payload = $this->payload();
        // Reserve the first generated candidate immediately before its existence query.
        // This exercises the real retry loop without replacing the random generator.
        DB::connection()->beforeExecuting(function ($sql, $bindings, $connection) use (&$reservedTicket, &$checks, $payload, $deleted, $legacy): void {
            if (! str_contains($sql, 'exists') || ! str_contains($sql, 'complaints') || ! str_contains($sql, 'tiket')) {
                return;
            }
            $checks++;
            if ($reservedTicket !== null) {
                return;
            }
            $reservedTicket = $bindings[0];
            $record = $payload + ['tiket' => $reservedTicket, 'status' => 'diterima', 'created_at' => now(), 'updated_at' => now()];
            if (! $legacy) {
                $record['deleted_at'] = $deleted ? now() : null;
            }
            $connection->table('complaints')->insert($record);
        });

        try {
            $response = $this->postJson('/api/complaints', $payload)->assertCreated();
            $this->assertNotNull($reservedTicket);
            $this->assertNotSame($reservedTicket, $response->json('data.tiket'));
            $this->assertGreaterThanOrEqual(2, $checks);
            $this->assertDatabaseCount('complaints', 2);
            $this->assertDatabaseHas('complaints', ['tiket' => $reservedTicket]);
            if ($deleted) {
                $this->assertSoftDeleted('complaints', ['tiket' => $reservedTicket]);
            }
        } finally {
            if ($legacy) {
                Schema::table('complaints', fn (Blueprint $table) => $table->renameColumn('pending_deleted_at', 'deleted_at'));
            }
        }
    }
}
