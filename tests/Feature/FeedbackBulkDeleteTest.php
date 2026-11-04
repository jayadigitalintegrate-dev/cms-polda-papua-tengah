<?php

namespace Tests\Feature;

use App\Models\FeedbackSubmission;
use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Tests\TestCase;

class FeedbackBulkDeleteTest extends TestCase
{
    private const ENDPOINT = '/kritik-saran/bulk-delete';

    protected function setUp(): void
    {
        // Tests\TestCase rejects non-memory DB settings before boot/traits.
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        config(['logging.default' => 'null', 'app.debug' => false]);
        $this->withoutVite();

        // Only disposable memory schema. Do not run artisan migrate/fresh.
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_07_31_020827_add_role_to_users_table.php',
            '2026_08_08_112455_create_site_settings_table.php',
            '2026_08_08_115009_add_logo_path_to_site_settings_table.php',
            '2026_10_14_100000_create_feedback_submissions_table.php',
            '2026_11_03_140000_create_feedback_deletion_audits_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    private function feedback(array $attributes = []): FeedbackSubmission
    {
        return FeedbackSubmission::create(array_merge([
            'category' => 'saran',
            'rating' => 4,
            'message' => 'Pesan fixture disposable.',
            'consent_at' => now(),
            'consent_version' => 'v1',
            'status' => 'baru',
        ], $attributes));
    }

    private function login(string $role = 'superadmin'): User
    {
        $user = User::factory()->create(['role' => $role]);
        $this->actingAs($user);
        return $user;
    }

    private function payload(array $context = []): array
    {
        $context = array_merge([
            'page' => 1, 'search' => '', 'category' => '', 'status' => '',
        ], $context);
        $response = $this->get('/kritik-saran?'.http_build_query($context));
        $response->assertOk();
        $this->withSession(['_token' => 'bulk-delete-test-csrf']);

        return array_merge($context, [
            '_token' => 'bulk-delete-test-csrf',
            'ids' => $response->viewData('feedbacks')->modelKeys(),
            'page_scope' => $response->viewData('pageScope'),
        ]);
    }

    private function assertIntact(int $count): void
    {
        $this->assertDatabaseCount('feedback_submissions', $count);
        $this->assertDatabaseCount('feedback_deletion_audits', 0);
    }

    private function assertControlledConflict(
        TestResponse $response,
        array $query = ['page' => 1],
        bool $auditUnavailable = false
    ): void {
        $message = $auditUnavailable
            ? 'Penghapusan belum dapat dilakukan karena sistem pencatatan '
                .'audit belum siap. Tidak ada data yang dihapus.'
            : 'Penghapusan tidak dapat diproses karena persyaratan keamanan '
                .'belum terpenuhi. Tidak ada data yang dihapus.';

        $response->assertRedirect(route('feedback.index', $query))
            ->assertSessionHas('feedback_delete_error', $message)
            ->assertSessionMissing('success');
        $this->assertSame(0, DB::connection()->transactionLevel());
        $this->assertFalse(DB::connection()->getPdo()->inTransaction());
    }

    public function test_guest_operator_supervisi_and_unknown_role_cannot_delete(): void
    {
        $feedback = $this->feedback();
        $this->delete(self::ENDPOINT)->assertRedirect('/login');
        foreach (['operator', 'supervisi', 'unknown'] as $role) {
            $this->login($role);
            $this->withSession(['_token' => 'csrf'])
                ->delete(self::ENDPOINT, ['_token' => 'csrf', 'ids' => [$feedback->id]])
                ->assertForbidden();
        }
        $this->assertIntact(1);
    }

    public function test_csrf_is_mandatory_even_for_same_origin_and_test_requests(): void
    {
        $this->feedback();
        $this->login();
        $payload = $this->payload();
        unset($payload['_token']);
        $this->withHeader('Sec-Fetch-Site', 'same-origin')
            ->delete(self::ENDPOINT, $payload)->assertStatus(419);
        $payload['_token'] = 'wrong';
        $this->delete(self::ENDPOINT, $payload)->assertStatus(419);
        $this->assertIntact(1);
    }

    public function test_superadmin_deletes_only_selected_rows_with_minimal_atomic_audit(): void
    {
        $first = $this->feedback(['email' => 'private@example.test']);
        $second = $this->feedback();
        $actor = $this->login();
        $payload = $this->payload();
        $payload['ids'] = [$first->id];
        $this->delete(self::ENDPOINT, $payload)
            ->assertRedirect(route('feedback.index', [
                'page' => 1, 'search' => '', 'category' => '', 'status' => '',
            ]))->assertSessionHas('success')->assertSessionMissing('feedback_delete_error');

        $this->assertDatabaseMissing('feedback_submissions', ['id' => $first->id]);
        $this->assertDatabaseHas('feedback_submissions', ['id' => $second->id]);
        $this->assertDatabaseCount('feedback_deletion_audits', 1);
        $audit = (array) DB::table('feedback_deletion_audits')->first();
        $this->assertEqualsCanonicalizing([
            'id', 'operation_uuid', 'actor_user_id', 'feedback_id', 'deleted_at', 'action_type',
        ], array_keys($audit));
        $this->assertSame($actor->id, $audit['actor_user_id']);
        $this->assertSame($first->id, $audit['feedback_id']);
        $this->assertSame('hard_delete', $audit['action_type']);
        $this->assertTrue(\Illuminate\Support\Str::isUuid($audit['operation_uuid']));
        $this->assertNotEmpty($audit['deleted_at']);
    }

    public function test_invalid_ids_reject_the_entire_batch(): void
    {
        $feedback = $this->feedback();
        $this->login();
        $payload = $this->payload();
        foreach ([null, [], '1', [0], [-1], ['1.5'], ['text'], [true],
            [$feedback->id, $feedback->id], [$feedback->id, 999999], range(1, 16),
        ] as $invalid) {
            $this->delete(self::ENDPOINT, array_replace($payload, ['ids' => $invalid]))
                ->assertRedirect()->assertSessionHasErrors();
            $this->assertIntact(1);
        }
        unset($payload['ids']);
        $this->delete(self::ENDPOINT, $payload)->assertRedirect()->assertSessionHasErrors('ids');
        $this->assertIntact(1);
    }

    public function test_signed_scope_rejects_tampering_expiry_actor_and_context_changes(): void
    {
        $this->feedback();
        $this->login();
        $payload = $this->payload();
        foreach ([
            ['page_scope' => 'invalid'],
            ['page_scope' => $payload['page_scope'].'x'],
            ['page' => 2], ['search' => 'changed'],
            ['category' => 'kritik'], ['status' => 'diarsipkan'],
        ] as $change) {
            $this->delete(self::ENDPOINT, array_replace($payload, $change))
                ->assertRedirect()->assertSessionHasErrors('page_scope');
        }
        $this->login();
        $this->delete(self::ENDPOINT, $payload)->assertRedirect()->assertSessionHasErrors('page_scope');
        $payload = $this->payload();
        $this->travel(11)->minutes();
        $this->delete(self::ENDPOINT, $payload)->assertRedirect()->assertSessionHasErrors('page_scope');
        $this->travelBack();
        $this->assertIntact(1);
    }

    public function test_scope_exposes_no_search_pii_and_disallows_ids_outside_filter(): void
    {
        $matching = $this->feedback(['email' => 'sensitive@example.test']);
        $outside = $this->feedback(['category' => 'kritik']);
        $this->login();
        $payload = $this->payload(['search' => 'sensitive@example.test', 'category' => 'saran']);
        $decoded = base64_decode(explode('.', $payload['page_scope'])[0], true);
        $this->assertStringNotContainsString('sensitive', $decoded);
        $this->assertSame([$matching->id], $payload['ids']);
        $payload['ids'] = [$outside->id];
        $this->delete(self::ENDPOINT, $payload)->assertRedirect()->assertSessionHasErrors('page_scope');
        $this->assertIntact(2);
    }

    public function test_page_scope_and_select_all_are_limited_to_active_page(): void
    {
        for ($i = 0; $i < 16; $i++) {
            $this->feedback();
        }
        $this->login();
        $firstPage = $this->payload();
        $secondPage = $this->payload(['page' => 2]);
        $this->assertCount(15, $firstPage['ids']);
        $this->assertCount(1, $secondPage['ids']);
        $forged = array_replace($firstPage, ['ids' => $secondPage['ids']]);
        $this->delete(self::ENDPOINT, $forged)->assertRedirect()->assertSessionHasErrors('page_scope');
        $this->assertIntact(16);

        $this->delete(self::ENDPOINT, $secondPage)
            ->assertRedirect(route('feedback.index', [
                'page' => 2, 'search' => '', 'category' => '', 'status' => '',
            ]));
        $this->assertDatabaseCount('feedback_submissions', 15);
        $this->assertDatabaseCount('feedback_deletion_audits', 1);
    }

    public function test_fifteen_selected_rows_share_one_operation_uuid(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $this->feedback();
        }
        $this->login();
        $this->delete(self::ENDPOINT, $this->payload())->assertRedirect();
        $this->assertDatabaseCount('feedback_submissions', 0);
        $this->assertDatabaseCount('feedback_deletion_audits', 15);
        $this->assertSame(1, DB::table('feedback_deletion_audits')->distinct()->count('operation_uuid'));
    }

    public function test_missing_audit_table_fails_closed(): void
    {
        $this->feedback();
        $this->login();
        $payload = $this->payload();
        Schema::drop('feedback_deletion_audits');
        $mutations = [];
        DB::listen(function (QueryExecuted $event) use (&$mutations): void {
            if (preg_match('/\A\s*(insert|update|delete)\b/i', $event->sql) === 1) {
                $mutations[] = $event->sql;
            }
        });
        $response = $this->withSession(['success' => 'Stale success'])
            ->delete(self::ENDPOINT, $payload);
        $this->assertControlledConflict($response, auditUnavailable: true);
        $this->assertDatabaseCount('feedback_submissions', 1);
        $this->assertFalse(Schema::hasTable('feedback_deletion_audits'));
        $this->assertSame([], $mutations);
        $this->get($response->headers->get('Location'))->assertOk()
            ->assertSee('role="alert"', false)
            ->assertSee('sistem pencatatan audit belum siap.')
            ->assertSee('Tidak ada data yang dihapus.')
            ->assertDontSee('Stale success')
            ->assertDontSee('Feedback deletion safety conflict.');
    }

    public function test_audit_insert_failure_prevents_delete(): void
    {
        $this->feedback();
        $this->login();
        $payload = $this->payload();
        DB::statement("CREATE UNIQUE INDEX reject_audit ON feedback_deletion_audits(action_type)");
        DB::table('feedback_deletion_audits')->insert([
            'operation_uuid' => (string) \Illuminate\Support\Str::uuid(),
            'actor_user_id' => auth()->id(), 'feedback_id' => 999,
            'deleted_at' => now(), 'action_type' => 'hard_delete',
        ]);
        $this->delete(self::ENDPOINT, $payload)->assertStatus(500)
            ->assertSessionMissing('feedback_delete_error')->assertSessionMissing('success');
        $this->assertDatabaseCount('feedback_submissions', 1);
        $this->assertDatabaseCount('feedback_deletion_audits', 1);
        $this->assertSame(0, DB::connection()->transactionLevel());
        $this->assertFalse(DB::connection()->getPdo()->inTransaction());
    }

    public function test_delete_failure_rolls_back_both_delete_and_audit(): void
    {
        $this->feedback();
        $this->login();
        $payload = $this->payload();
        DB::listen(function (QueryExecuted $event): void {
            if (str_starts_with(strtolower($event->sql), 'delete from "feedback_submissions"')) {
                throw new \RuntimeException('Simulated transaction failure.');
            }
        });
        $this->delete(self::ENDPOINT, $payload)->assertStatus(500)
            ->assertSessionMissing('feedback_delete_error')->assertSessionMissing('success');
        $this->assertIntact(1);
        $this->assertSame(0, DB::connection()->transactionLevel());
        $this->assertFalse(DB::connection()->getPdo()->inTransaction());
    }

    public function test_exact_delete_count_mismatch_rolls_back_audit_and_rows(): void
    {
        $feedback = $this->feedback();
        $this->login();
        $payload = $this->payload();
        DB::listen(function (QueryExecuted $event) use ($feedback): void {
            if ($event->connection->transactionLevel() > 0
                && str_contains($event->sql, 'count(*)')
                && str_contains($event->sql, '"feedback_submissions"')) {
                // Simulate a target disappearing after the count was read.
                DB::table('feedback_submissions')->where('id', $feedback->id)->delete();
            }
        });
        $this->assertControlledConflict($this->delete(self::ENDPOINT, $payload));
        $this->assertIntact(1);
    }

    public function test_disabled_effective_foreign_keys_fail_closed(): void
    {
        $this->feedback();
        $this->login();
        $payload = $this->payload();
        DB::statement('PRAGMA foreign_keys = OFF');
        $this->assertControlledConflict($this->delete(self::ENDPOINT, $payload));
        $this->assertIntact(1);
    }

    public function test_inbound_foreign_key_blocks_cascade_even_if_table_is_empty(): void
    {
        $this->feedback();
        $this->login();
        $payload = $this->payload();
        Schema::create('feedback_cascade_fixture', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('feedback_submission_id')
                ->unique()
                ->constrained('feedback_submissions')
                ->cascadeOnDelete();
        });
        $this->assertControlledConflict($this->delete(self::ENDPOINT, $payload));
        $this->assertIntact(1);
    }

    public function test_related_rows_cannot_be_cascaded_or_nullified(): void
    {
        $feedback = $this->feedback();
        $this->login();
        $payload = $this->payload();
        Schema::create('dependent_fixture', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('feedback_id')->nullable()
                ->constrained('feedback_submissions')->nullOnDelete();
        });
        DB::table('dependent_fixture')->insert(['feedback_id' => $feedback->id]);
        $this->assertControlledConflict($this->delete(self::ENDPOINT, $payload));
        $this->assertDatabaseHas('dependent_fixture', ['feedback_id' => $feedback->id]);
        $this->assertIntact(1);
    }

    public function test_unreviewed_triggers_fail_closed(): void
    {
        $this->feedback();
        $this->login();
        $payload = $this->payload();
        DB::unprepared('CREATE TRIGGER delete_side_effect AFTER DELETE ON feedback_submissions BEGIN SELECT 1; END');
        $this->assertControlledConflict($this->delete(self::ENDPOINT, $payload));
        $this->assertIntact(1);
    }

    public function test_false_audit_insert_fails_closed_with_a_friendly_error(): void
    {
        $this->feedback();
        $this->login();
        $payload = $this->payload();
        $original = DB::connection();

        // Reuse only this test's disposable PDO; inject the false-return branch.
        $failing = new class($original->getPdo(), ':memory:', '', $original->getConfig()) extends SQLiteConnection
        {
            public int $auditInsertAttempts = 0;

            public function insert($query, $bindings = [])
            {
                if (str_starts_with($query, 'insert into "feedback_deletion_audits"')) {
                    $this->auditInsertAttempts++;

                    return false;
                }

                return parent::insert($query, $bindings);
            }
        };
        DB::extend(DB::getDefaultConnection(), static fn () => $failing);
        DB::purge();

        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->assertControlledConflict($this->delete(self::ENDPOINT, $payload));
        $this->assertSame(1, $failing->auditInsertAttempts);
        $this->assertIntact(1);
    }

    public function test_unexpected_conflict_exception_propagates_unchanged(): void
    {
        $this->feedback();
        $this->login();
        $payload = $this->payload();
        $unexpected = new ConflictHttpException('Unexpected fixture conflict.');
        DB::connection()->beforeExecuting(function (string $sql) use ($unexpected): void {
            if (str_starts_with($sql, 'insert into "feedback_deletion_audits"')) {
                throw $unexpected;
            }
        });
        $this->withoutExceptionHandling();
        $caught = null;

        try {
            $this->delete(self::ENDPOINT, $payload);
        } catch (ConflictHttpException $exception) {
            $caught = $exception;
        }

        $this->assertSame($unexpected, $caught);
        $this->assertFalse(session()->has('feedback_delete_error'));
        $this->assertFalse(session()->has('success'));
        $this->assertSame(0, DB::connection()->transactionLevel());
        $this->assertFalse(DB::connection()->getPdo()->inTransaction());
        $this->assertIntact(1);
    }

    public function test_target_count_change_before_delete_rolls_back(): void
    {
        $feedback = $this->feedback();
        $this->login();
        $payload = $this->payload();
        $changed = false;
        DB::connection()->beforeExecuting(function (string $sql) use ($feedback, &$changed): void {
            if (! $changed && DB::connection()->transactionLevel() > 0
                && str_contains($sql, 'count(*)')
                && str_contains($sql, '"feedback_submissions"')) {
                $changed = true;
                DB::table('feedback_submissions')->where('id', $feedback->id)->delete();
            }
        });

        $this->assertControlledConflict($this->delete(self::ENDPOINT, $payload));
        $this->assertTrue($changed);
        $this->assertIntact(1);
    }

    public function test_conflict_preserves_valid_search_filters_and_page(): void
    {
        for ($i = 0; $i < 16; $i++) {
            $this->feedback([
                'message' => 'scope-fixture', 'category' => 'kritik', 'status' => 'ditinjau',
            ]);
        }
        $this->login();
        $query = ['page' => 2, 'search' => 'scope-fixture', 'category' => 'kritik', 'status' => 'ditinjau'];
        $payload = $this->payload($query);
        $this->assertCount(1, $payload['ids']);
        Schema::drop('feedback_deletion_audits');

        $this->assertControlledConflict(
            $this->delete(self::ENDPOINT, $payload), $query, auditUnavailable: true
        );
        $this->assertDatabaseCount('feedback_submissions', 16);
        $this->assertFalse(Schema::hasTable('feedback_deletion_audits'));
    }

    public function test_conflict_redirect_ignores_invalid_filters_and_unknown_parameters(): void
    {
        $this->feedback();
        $this->login();
        $payload = $this->payload([
            'category' => 'unknown', 'status' => 'unknown',
            'redirect' => 'https://example.invalid/elsewhere', 'q' => 'ignored',
        ]);
        Schema::drop('feedback_deletion_audits');

        $response = $this->withHeader('Referer', 'https://example.invalid/elsewhere')
            ->delete(self::ENDPOINT, $payload);
        $this->assertControlledConflict($response, auditUnavailable: true);
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertSame(['page' => '1'], $query);
        $this->assertDatabaseCount('feedback_submissions', 1);
    }

    public function test_invalid_page_is_rejected_before_conflict_handling(): void
    {
        $this->feedback();
        $this->login();
        $payload = $this->payload();

        foreach ([0, -1, 'invalid', [], null] as $page) {
            $this->delete(self::ENDPOINT, array_replace($payload, ['page' => $page]))
                ->assertRedirect()->assertSessionHasErrors('page')
                ->assertSessionMissing('feedback_delete_error')->assertSessionMissing('success');
            $this->assertIntact(1);
        }
    }

    public function test_ui_has_cancel_guard_indeterminate_and_role_restriction(): void
    {
        $this->feedback();
        $this->login();
        $this->get('/kritik-saran')->assertOk()
            ->assertSee('Hapus Terpilih')->assertSee('window.confirm', false)
            ->assertSee('$event.preventDefault()', false)
            ->assertSee('$el.indeterminate', false)
            ->assertSee('Pilih semua masukan di halaman ini')
            ->assertSee('name="ids[]"', false);
        foreach (['operator', 'supervisi'] as $role) {
            $this->login($role);
            $this->get('/kritik-saran')->assertOk()
                ->assertDontSee('Hapus Terpilih')
                ->assertDontSee('name="ids[]"', false);
        }
        $this->assertIntact(1);
    }
}
