<?php

namespace App\Http\Controllers;

use App\Models\PpidRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PPIDRequestController extends Controller
{
    /**
     * Menampilkan daftar permohonan informasi PPID.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');

        $allowedStatuses = [
            'diterima',
            'diverifikasi',
            'diproses',
            'selesai',
            'ditolak',
        ];

        $ppidRequests = PpidRequest::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('ticket', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere(
                            'identity_number',
                            'like',
                            '%' . $search . '%'
                        );
                });
            })
            ->when(
                in_array($status, $allowedStatuses, true),
                function ($query) use ($status) {
                    $query->where('status', $status);
                }
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(
            'admin.ppid_requests.index',
            compact(
                'ppidRequests',
                'search',
                'status'
            )
        );
    }

    /**
     * Menampilkan detail satu permohonan informasi PPID.
     */
    public function show(PpidRequest $ppidRequest): View
    {
        return view(
            'admin.ppid_requests.show',
            compact('ppidRequest')
        );
    }

    /**
     * Menghapus beberapa permohonan informasi PPID sekaligus.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],
            'ids.*' => [
                'integer',
                'distinct',
                'exists:ppid_requests,id',
            ],
        ]);

        $deleted = PpidRequest::query()
            ->whereIn('id', $validated['ids'])
            ->delete();

        return redirect()
            ->route('ppid-requests.index')
            ->with(
                'success',
                $deleted . ' permohonan PPID berhasil dihapus.'
            );
    }

/**
 * Export beberapa permohonan informasi PPID ke PDF.
 */
public function exportPdf(Request $request): Response
{
    $validated = $request->validate([
        'ids' => [
            'required',
            'array',
            'min:1',
            'max:100',
        ],
        'ids.*' => [
            'integer',
            'distinct',
            'exists:ppid_requests,id',
        ],
    ]);

    $ppidRequests = PpidRequest::query()
        ->whereIn('id', $validated['ids'])
        ->with('processor')
        ->orderByDesc('created_at')
        ->get();

    $pdf = Pdf::loadView(
        'admin.ppid_requests.pdf',
        compact('ppidRequests')
    );

    $pdf->setPaper('a4', 'portrait');

    return $pdf->download(
        'permohonan-ppid-' . now()->format('Ymd-His') . '.pdf'
    );
}



    /**
     * Memperbarui status dan catatan permohonan PPID.
     */
    public function update(
        Request $request,
        PpidRequest $ppidRequest
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                'in:diterima,diverifikasi,diproses,selesai,ditolak',
            ],
            'catatan_admin' => [
                'nullable',
                'string',
            ],
        ]);

        $ppidRequest->status = $validated['status'];
        $ppidRequest->catatan_admin = $validated['catatan_admin'] ?? null;

        if ($ppidRequest->isDirty('status')) {
            $ppidRequest->processed_by = auth()->id();
            $ppidRequest->processed_at = now();
        }

        $ppidRequest->save();

        return redirect()
            ->route('ppid-requests.show', $ppidRequest)
            ->with(
                'success',
                'Status permohonan PPID berhasil diperbarui.'
            );
    }
}