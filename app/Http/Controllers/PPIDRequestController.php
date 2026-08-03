<?php

namespace App\Http\Controllers;

use App\Models\PpidRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PPIDRequestController extends Controller
{
    /**
     * Menampilkan daftar permohonan informasi PPID.
     */
    public function index(): View
    {
        $ppidRequests = PpidRequest::query()
            ->latest()
            ->paginate(15);

        return view(
            'admin.ppid_requests.index',
            compact('ppidRequests')
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
            ->with('success', 'Status permohonan PPID berhasil diperbarui.');
    }
}