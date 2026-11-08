<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    use SoftDeletes;

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        $query = parent::resolveRouteBindingQuery($query, $value, $field);

        // Legacy installations can still display details before soft deletes are migrated.
        // Check on each binding so a missing column is never cached across migrations.
        if (! $this->getConnection()->getSchemaBuilder()->hasColumn($this->getTable(), $this->getDeletedAtColumn())) {
            $query->withoutGlobalScope(SoftDeletingScope::class);
        }

        return $query;
    }

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
