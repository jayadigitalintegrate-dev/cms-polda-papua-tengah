<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ComplaintController extends Controller
{
    /**
     * Menerima pengaduan masyarakat dari website.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
            ],

            'ktp' => [
                'required',
                'string',
                'max:50',
            ],

            'hp' => [
                'required',
                'string',
                'max:30',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'alamat' => [
                'required',
                'string',
            ],

            'jenis' => [
                'required',
                'string',
                'max:255',
            ],

            'isi' => [
                'required',
                'string',
            ],
        ]);

        do {
            $ticket = 'PPT-'
                . now()->format('Ymd')
                . '-'
                . str_pad(
                    (string) random_int(0, 999999),
                    6,
                    '0',
                    STR_PAD_LEFT
                );
        } while (
            Complaint::where('tiket', $ticket)->exists()
        );

        $complaint = Complaint::create([
            'nama' => $validated['nama'],
            'ktp' => $validated['ktp'],
            'hp' => $validated['hp'],
            'email' => $validated['email'],
            'alamat' => $validated['alamat'],
            'jenis' => $validated['jenis'],
            'isi' => $validated['isi'],
            'tiket' => $ticket,
            'status' => 'diterima',
        ]);

        return response()->json([
            'message' => 'Pengaduan berhasil diterima.',
            'data' => [
                'id' => $complaint->id,
                'tiket' => $complaint->tiket,
                'status' => $complaint->status,
                'tanggal' => $complaint->created_at,
            ],
        ], 201);
    }
}