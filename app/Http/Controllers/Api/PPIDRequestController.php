<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PpidRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PPIDRequestController extends Controller
{
    /**
     * Menerima permohonan informasi publik dari website.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'identity_number' => [
                'required',
                'string',
                'max:50',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
            ],

            'address' => [
                'required',
                'string',
            ],

            'information' => [
                'required',
                'string',
            ],

            'purpose' => [
                'required',
                'string',
            ],

            'delivery_method' => [
                'required',
                'in:softcopy,hardcopy,view',
            ],
        ]);

        do {
            $ticket = 'PPT-PPID-'
                . now()->format('Ymd')
                . '-'
                . str_pad(
                    (string) random_int(0, 999999),
                    6,
                    '0',
                    STR_PAD_LEFT
                );
        } while (
            PpidRequest::where('ticket', $ticket)->exists()
        );

        $ppidRequest = PpidRequest::create([
            'name' => $validated['name'],
            'identity_number' => $validated['identity_number'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'information' => $validated['information'],
            'purpose' => $validated['purpose'],
            'delivery_method' => $validated['delivery_method'],
            'ticket' => $ticket,
            'status' => 'diterima',
        ]);

        return response()->json([
            'message' => 'Permohonan informasi publik berhasil diterima.',
            'data' => [
                'id' => $ppidRequest->id,
                'ticket' => $ppidRequest->ticket,
                'status' => $ppidRequest->status,
                'tanggal' => $ppidRequest->created_at,
            ],
        ], 201);
    }
}
