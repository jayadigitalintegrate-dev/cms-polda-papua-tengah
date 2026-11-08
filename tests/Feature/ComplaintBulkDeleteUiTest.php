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

class ComplaintBulkDeleteUiTest extends TestCase
{
    use RefreshDatabase;

    private function complaint(int $number = 1): Complaint
    {
        return Complaint::create([
            'nama' => 'Pelapor UI', 'ktp' => 'UI-'.$number,
            'hp' => '080000000000', 'email' => 'ui@example.test',
            'alamat' => 'Alamat tes', 'jenis' => 'Pengujian',
            'isi' => 'Pengujian UI', 'tiket' => 'UI-'.$number,
            'status' => 'diterima',
        ]);
    }

    public function test_superadmin_sees_ready_controls_and_compiled_blade(): void
    {
        $this->complaint();
        $this->actingAs(User::factory()->create(['role' => 'superadmin']))
            ->get(route('complaints.index'))->assertOk()
            ->assertSee('id="complaint-bulk-delete"', false)
            ->assertSee('method="POST"', false)
            ->assertSee('action="'.route('complaints.bulk-delete').'"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('Pilih Semua')->assertSee('Hapus Terpilih')
            ->assertDontSee('name="reason"', false)
            ->assertDontSee('complaint-deletion-reason')
            ->assertDontSee('Alasan penghapusan')
            ->assertSee('type="submit" disabled', false)
            ->assertSee('0 pengaduan dipilih')
            ->assertSee('Lihat Detail')->assertSee('UI-1');
    }

    public static function readOnlyRoles(): array
    {
        return [['operator'], ['supervisi']];
    }

    #[DataProvider('readOnlyRoles')]
    public function test_read_only_roles_do_not_see_delete_controls(string $role): void
    {
        $this->complaint();
        $this->actingAs(User::factory()->create(['role' => $role]))
            ->get(route('complaints.index'))->assertOk()
            ->assertDontSee('complaint-bulk-delete')->assertDontSee('Pilih Semua')
            ->assertDontSee('Hapus Terpilih')->assertSee('Lihat Detail');
    }

    public function test_viewer_cannot_view_index(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'viewer']))
            ->get(route('complaints.index'))->assertForbidden();
    }

    public function test_selection_inputs_contain_only_current_page_ids(): void
    {
        for ($i = 1; $i <= 16; $i++) {
            $this->complaint($i);
        }
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));
        $pages = [];
        foreach ([1 => 15, 2 => 1] as $page => $expectedCount) {
            $response = $this->get(route('complaints.index', ['page' => $page]))->assertOk();
            preg_match_all('/name="ids\[\]" value="(\d+)"/', $response->getContent(), $matches);
            $this->assertCount($expectedCount, $matches[1]);
            $this->assertSame(array_map('strval', $response->viewData('complaints')->pluck('id')->all()), $matches[1]);
            $pages[] = $matches[1];
        }
        $this->assertSame([], array_intersect($pages[0], $pages[1]));
    }

    public function test_blank_selection_shows_validation_errors(): void
    {
        $complaint = $this->complaint();
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));
        $this->followingRedirects()->from(route('complaints.index'))
            ->post(route('complaints.bulk-delete'), [])
            ->assertOk()->assertSee('role="alert"', false);
        $this->assertNotNull(Complaint::find($complaint->id));
        $this->assertDatabaseCount('complaint_deletion_audits', 0);
    }

    public function test_valid_submission_redirects_to_success_message(): void
    {
        $complaint = $this->complaint();
        $this->actingAs(User::factory()->create(['role' => 'superadmin']))
            ->post(route('complaints.bulk-delete'), ['ids' => [$complaint->id]])
            ->assertRedirect(route('complaints.index'))->assertSessionHas('success');
        $this->get(route('complaints.index'))->assertOk()
            ->assertSee('Laporan terpilih berhasil dihapus secara lunak.')
            ->assertDontSee('data-complaint-selection aria-label', false);
        $this->assertSoftDeleted('complaints', ['id' => $complaint->id]);
        $this->assertDatabaseCount('complaints', 1);
        $this->assertDatabaseCount('complaint_deletion_audits', 1);
    }

    public static function unavailableSchema(): array
    {
        return [['column'], ['audit table'], ['migration record']];
    }

    #[DataProvider('unavailableSchema')]
    public function test_controls_stay_hidden_and_post_is_rejected_before_migrations_are_ready(string $missing): void
    {
        $complaint = $this->complaint();
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));
        // Change only the guarded in-memory test schema; restore it even on failure.
        if ($missing === 'column') {
            Schema::table('complaints', fn (Blueprint $table) => $table->renameColumn('deleted_at', 'pending_deleted_at'));
        } elseif ($missing === 'audit table') {
            Schema::rename('complaint_deletion_audits', 'pending_complaint_deletion_audits');
        } else {
            DB::table('migrations')->where('migration', '2026_11_07_183000_create_complaint_deletion_audits_table')
                ->update(['migration' => 'pending_complaint_deletion_audits']);
        }
        try {
            $this->get(route('complaints.index'))->assertOk()->assertSee('UI-1')
                ->assertSee('Fitur penghapusan pengaduan belum tersedia.')
                ->assertDontSee('id="complaint-bulk-delete"', false)
                ->assertDontSee('data-complaint-selection')->assertDontSee('Pilih Semua');
            $this->post(route('complaints.bulk-delete'), ['ids' => [$complaint->id]])
                ->assertStatus(503);
            $column = $missing === 'column' ? 'pending_deleted_at' : 'deleted_at';
            $this->assertNull(DB::table('complaints')->where('id', $complaint->id)->value($column));
            $this->assertDatabaseCount('complaints', 1);
            $this->assertDatabaseCount($missing === 'audit table' ? 'pending_complaint_deletion_audits' : 'complaint_deletion_audits', 0);
        } finally {
            if ($missing === 'column') {
                Schema::table('complaints', fn (Blueprint $table) => $table->renameColumn('pending_deleted_at', 'deleted_at'));
            } elseif ($missing === 'audit table') {
                Schema::rename('pending_complaint_deletion_audits', 'complaint_deletion_audits');
            } else {
                DB::table('migrations')->where('migration', 'pending_complaint_deletion_audits')
                    ->update(['migration' => '2026_11_07_183000_create_complaint_deletion_audits_table']);
            }
        }
    }
}
