<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    protected $fillable = [
        'nama',
        'ktp',
        'hp',
        'email',
        'alamat',
        'jenis',
        'isi',
        'tiket',
        'status',
        'catatan_admin',
        'processed_by',
        'processed_at',
    ];

    /**
     * Petugas/admin yang memproses pengaduan.
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