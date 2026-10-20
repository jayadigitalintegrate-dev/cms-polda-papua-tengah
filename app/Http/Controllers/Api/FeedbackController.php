<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeedbackRequest;
use App\Models\FeedbackSubmission;
use Illuminate\Http\JsonResponse;

class FeedbackController extends Controller
{
    /**
     * Menerima masukan Kritik & Saran dari website.
     *
     * Endpoint ini hanya mencatat penerimaan. Status penanganan dikelola
     * admin dan tidak pernah dikirim kembali ke publik.
     */
    public function store(StoreFeedbackRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $feedback = FeedbackSubmission::create([
            'category' => $validated['category'],
            'rating' => $validated['rating'],
            'message' => $validated['message'],
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'consent_at' => now(),
            'consent_version' => FeedbackSubmission::CONSENT_VERSION,
            'ip_hash' => $request->ip()
                ? hash_hmac('sha256', $request->ip(), (string) config('app.key'))
                : null,
            'status' => 'baru',
        ]);

        return response()->json([
            'message' => 'Masukan berhasil diterima.',
            'data' => [
                'received_at' => $feedback->created_at->toIso8601String(),
            ],
        ], 201);
    }
}
