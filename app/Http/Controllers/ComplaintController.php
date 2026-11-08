<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintStatusHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ComplaintController extends Controller
{
    private function deletionFeatureReady(): bool
    {
        $schema = \Illuminate\Support\Facades\Schema::class;

        if (! $schema::hasTable('complaint_deletion_audits')
            || ! $schema::hasColumn('complaints', 'deleted_at')
            || ! $schema::hasTable('migrations')) {
            return false;
        }

        $required = [
            '2026_11_07_182000_add_soft_deletes_to_complaints_table',
            '2026_11_07_183000_create_complaint_deletion_audits_table',
        ];

        return DB::table('migrations')
            ->whereIn('migration', $required)
            ->distinct()
            ->count('migration') === count($required);
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        Gate::authorize('delete-complaints');

        abort_unless(
            $this->deletionFeatureReady(),
            503,
            'Fitur penghapusan pengaduan belum siap.'
        );

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:15'],
            'ids.*' => ['required', 'integer', 'distinct', 'min:1'],
        ]);

        $ids = array_map('intval', $validated['ids']);
        $actorId = $request->user()->getAuthIdentifier();

        DB::transaction(function () use ($ids, $actorId): void {
            $complaints = Complaint::query()
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get();

            if ($complaints->count() !== count($ids)) {
                throw ValidationException::withMessages([
                    'ids' => 'Pilihan berisi laporan yang tidak tersedia atau sudah dihapus.',
                ]);
            }

            foreach ($complaints as $complaint) {
                $complaint->delete();

                \App\Models\ComplaintDeletionAudit::create([
                    'complaint_id' => $complaint->id,
                    'ticket' => $complaint->tiket,
                    'action' => 'soft_delete',
                    'actor_user_id' => $actorId,
                    'created_at' => now(),
                ]);
            }
        });

        return redirect()
            ->route('complaints.index')
            ->with('success', 'Laporan terpilih berhasil dihapus secara lunak.');
    }
    private function statusFeatureReady(): bool
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('complaint_status_histories')) {
            return false;
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('migrations')) {
            return false;
        }

        $required = [
            '2026_11_07_180000_expand_complaints_status_check',
            '2026_11_07_181000_create_complaint_status_histories_table',
        ];

        return \Illuminate\Support\Facades\DB::table('migrations')
            ->whereIn('migration', $required)
            ->distinct()
            ->count('migration') === count($required);
    }
    public const STATUSES = [
        'diterima',
        'diverifikasi',
        'diproses',
        'dikerjakan',
        'selesai',
        'ditolak',
    ];

    /**
     * Menampilkan daftar pengaduan masyarakat.
     */
    public function index()
    {
        $deletionFeatureReady = $this->deletionFeatureReady();

        $complaints = Complaint::query()
            // Before the soft-delete migration, the global scope references a missing column.
            ->when(! \Illuminate\Support\Facades\Schema::hasColumn('complaints', 'deleted_at'),
                fn ($query) => $query->withoutGlobalScope(\Illuminate\Database\Eloquent\SoftDeletingScope::class))
            ->latest()
            ->paginate(15);

        return view(
            'admin.complaints.index',
            compact('complaints', 'deletionFeatureReady')
        );
    }

    /**
     * Menampilkan detail satu pengaduan.
     */
    public function show(Complaint $complaint)
    {
        $statusFeatureReady = $this->statusFeatureReady();

        $statusHistories = collect();

        if ($statusFeatureReady) {
            $statusHistories = ComplaintStatusHistory::query()
                ->where('complaint_id', $complaint->id)
                ->with('actor')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get();
        }

        return view(
            'admin.complaints.show',
            compact('complaint', 'statusHistories', 'statusFeatureReady')
        );
    }

    /**
     * Memperbarui status dan mencatat riwayat perubahan.
     */
    public function updateStatus(
        Request $request,
        Complaint $complaint
    ): RedirectResponse {
        Gate::authorize('update-complaints');

        abort_unless(
            $this->statusFeatureReady(),
            503,
            'Fitur perubahan status belum siap.'
        );

        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in(self::STATUSES),
            ],
            'catatan_admin' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        $actorId = $request->user()->getAuthIdentifier();

        DB::transaction(function () use (
            $complaint,
            $validated,
            $actorId
        ): void {
            $record = Complaint::query()
                ->whereKey($complaint->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $oldStatus = $record->status;
            $newStatus = $validated['status'];

            if ($oldStatus === $newStatus) {
                throw ValidationException::withMessages([
                    'status' => 'Pilih status yang berbeda dari status saat ini.',
                ]);
            }

            $note = trim($validated['catatan_admin']);

            if ($note === '') {
                throw ValidationException::withMessages([
                    'catatan_admin' => 'Catatan tindak lanjut wajib diisi.',
                ]);
            }

            $timestamp = now();

            $record->status = $newStatus;
            $record->catatan_admin = $note;
            $record->processed_by = $actorId;
            $record->processed_at = $timestamp;

            $record->save();

            ComplaintStatusHistory::create([
                'complaint_id' => $record->id,
                'ticket' => $record->tiket,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'note' => $note,
                'actor_user_id' => $actorId,
            ]);
        });

        return redirect()
            ->route('complaints.show', $complaint)
            ->with(
                'success',
                'Status pengaduan berhasil diperbarui.'
            );
    }
}
