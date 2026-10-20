<?php

namespace App\Http\Controllers;

use App\Models\FeedbackSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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
            ->paginate(15)
            ->withQueryString();

        return view(
            'admin.feedback.index',
            compact(
                'feedbacks',
                'search',
                'category',
                'status'
            )
        );
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
