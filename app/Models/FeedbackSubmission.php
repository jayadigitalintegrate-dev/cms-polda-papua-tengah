<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackSubmission extends Model
{
    /**
     * Versi teks persetujuan yang ditampilkan di website.
     * Naikkan bila redaksi persetujuan berubah.
     */
    public const CONSENT_VERSION = 'v1';

    protected $fillable = [
        'category',
        'rating',
        'message',
        'name',
        'email',
        'phone',
        'consent_at',
        'consent_version',
        'ip_hash',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    /**
     * Admin/petugas yang meninjau masukan.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'consent_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }
}
