<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkFeedbackDeleteRequest;
use App\Models\FeedbackSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Dashboard operator Kritik & Saran. Hanya untuk pengguna CMS yang lolos
 * gate view-feedback (index/show) atau update-feedback (update), lihat
 * routes/web.php; tidak ada API publik untuk membaca masukan.
 */
class FeedbackSubmissionController extends Controller
{
    public const CATEGORIES = ['apresiasi', 'saran', 'kritik'];

    public const STATUSES = ['baru', 'ditinjau', 'ditindaklanjuti', 'diarsipkan'];

    /**
     * Menampilkan daftar masukan Kritik & Saran.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $category = $request->input('category');
        $status = $request->input('status');

        $feedbacks = FeedbackSubmission::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('message', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            })
            ->when(
                in_array($category, self::CATEGORIES, true),
                fn ($query) => $query->where('category', $category)
            )
            ->when(
                in_array($status, self::STATUSES, true),
                fn ($query) => $query->where('status', $status)
            )
            ->latest()
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $pageScope = $request->user()->can('delete-feedback')
            ? BulkFeedbackDeleteRequest::issueScope($request, $feedbacks->modelKeys())
            : null;

        return view(
            'admin.feedback.index',
            compact(
                'feedbacks',
                'search',
                'category',
                'status',
                'pageScope'
            )
        );
    }

    public function bulkDelete(BulkFeedbackDeleteRequest $request): RedirectResponse
    {
        $ids = array_map('intval', $request->validated('ids'));
        $connection = DB::connection();
        $initialLevel = $connection->transactionLevel();
        $expectedConflict = null;
        $auditUnavailable = false;

        $requireSafety = static function (
            bool $condition,
            bool $missingAudit = false
        ) use (&$expectedConflict, &$auditUnavailable): void {
            if ($condition) {
                return;
            }

            $auditUnavailable = $missingAudit;
            $expectedConflict = new ConflictHttpException('Feedback deletion safety conflict.');

            throw $expectedConflict;
        };

        try {
            // Other engines fail closed until their constraint inspection exists.
            $requireSafety($connection->getDriverName() === 'sqlite');

            $connection->transaction(function () use ($connection, $ids, $request, $requireSafety): void {
                $requireSafety((int) $connection->scalar('PRAGMA foreign_keys') === 1);
                $requireSafety(
                    $connection->getSchemaBuilder()->hasTable('feedback_deletion_audits'),
                    true
                );

                // Conservative: reject every inbound FK, even when currently empty.
                // This prevents CASCADE, SET NULL, and indirect related-row changes.
                foreach ($connection->select("SELECT name FROM main.sqlite_schema WHERE type = 'table'") as $table) {
                    $quoted = '"'.str_replace('"', '""', $table->name).'"';
                    foreach ($connection->select('PRAGMA main.foreign_key_list('.$quoted.')') as $foreignKey) {
                        $requireSafety(strcasecmp($foreignKey->table, 'feedback_submissions') !== 0);
                    }
                }
                $requireSafety(! $connection->table('sqlite_schema')->where('type', 'trigger')
                    ->whereIn('tbl_name', ['feedback_submissions', 'feedback_deletion_audits'])->exists());

                $targets = $connection->table('feedback_submissions')->whereIn('id', $ids);
                $requireSafety((clone $targets)->count() === count($ids));

                $operation = (string) Str::uuid();
                $timestamp = now();
                $audit = array_map(fn (int $id): array => [
                    'operation_uuid' => $operation,
                    'actor_user_id' => $request->user()->getAuthIdentifier(),
                    'feedback_id' => $id,
                    'deleted_at' => $timestamp,
                    'action_type' => 'hard_delete',
                ], $ids);

                $requireSafety($connection->table('feedback_deletion_audits')->insert($audit));
                $requireSafety($connection->table('feedback_deletion_audits')
                    ->where('operation_uuid', $operation)->count() === count($ids));
                $requireSafety($targets->delete() === count($ids));
            });
        } catch (ConflictHttpException $exception) {
            // The framework must finish rollback before a local guard becomes UX.
            // Unrelated 409 exceptions must continue through Laravel unchanged.
            if ($exception !== $expectedConflict
                || $connection->transactionLevel() !== $initialLevel) {
                throw $exception;
            }

            $message = $auditUnavailable
                ? 'Penghapusan belum dapat dilakukan karena sistem pencatatan '
                    .'audit belum siap. Tidak ada data yang dihapus.'
                : 'Penghapusan tidak dapat diproses karena persyaratan keamanan '
                    .'belum terpenuhi. Tidak ada data yang dihapus.';

            $request->session()->forget('success');

            return redirect()->route('feedback.index', $this->feedbackDeleteReturnQuery($request->validated()))
                ->with('feedback_delete_error', $message);
        }

        return redirect()->route('feedback.index', BulkFeedbackDeleteRequest::context($request))
            ->with('success', count($ids).' masukan berhasil dihapus permanen.');
    }

    private function feedbackDeleteReturnQuery(array $input): array
    {
        $query = ['page' => 1];
        $page = $input['page'] ?? null;

        if (is_int($page) || is_string($page)) {
            $normalizedPage = filter_var($page, FILTER_VALIDATE_INT, [
                'options' => [
                    'min_range' => 1,
                    'max_range' => intdiv(PHP_INT_MAX, 15),
                ],
            ]);

            if ($normalizedPage !== false) {
                $query['page'] = $normalizedPage;
            }
        }

        $search = $input['search'] ?? null;
        if (is_string($search)) {
            $search = trim($search);
            if ($search !== '' && mb_strlen($search) <= 2000
                && preg_match('/[\x00-\x1F\x7F]/', $search) === 0) {
                $query['search'] = $search;
            }
        }

        if (in_array($input['category'] ?? null, self::CATEGORIES, true)) {
            $query['category'] = $input['category'];
        }
        if (in_array($input['status'] ?? null, self::STATUSES, true)) {
            $query['status'] = $input['status'];
        }

        return $query;
    }

    /**
     * Menampilkan detail satu masukan, termasuk identitas opsional pengirim.
     */
    public function show(FeedbackSubmission $feedback): View
    {
        $feedback->load('reviewer');

        return view(
            'admin.feedback.show',
            compact('feedback')
        );
    }

    /**
     * Memperbarui status dan catatan tindak lanjut masukan.
     */
    public function update(
        Request $request,
        FeedbackSubmission $feedback
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(self::STATUSES),
            ],
            'admin_note' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ], [
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
            'admin_note.max' => 'Catatan maksimal :max karakter.',
        ]);

        $feedback->status = $validated['status'];
        $feedback->admin_note = $validated['admin_note'] ?? null;

        if ($feedback->isDirty(['status', 'admin_note'])) {
            $feedback->reviewed_by = $request->user()->id;
            $feedback->reviewed_at = now();
        }

        $feedback->save();

        return redirect()
            ->route('feedback.show', $feedback)
            ->with(
                'success',
                'Status masukan berhasil diperbarui.'
            );
    }
}
