<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpidRequest extends Model
{
    protected $fillable = [
        'name',
        'identity_number',
        'email',
        'phone',
        'address',
        'information',
        'purpose',
        'delivery_method',
        'ticket',
        'status',
        'catatan_admin',
        'processed_by',
        'processed_at',
    ];

    /**
     * Admin/petugas yang memproses permohonan.
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'processed_by'
        );
    }

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
        ];
    }
}