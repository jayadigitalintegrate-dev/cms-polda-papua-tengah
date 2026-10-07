<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalleryCategory extends Model
{
    /** Kategori koleksi multi-foto (1–5 foto per item). */
    public const DOCUMENTATION_SLUG = 'galeri-dokumentasi';

    /** Kategori konten editorial (judul, deskripsi, isi berita, 1–5 foto). */
    public const MEDIA_CENTER_SLUG = 'media-center';

    /** Jenis item Galeri berdasarkan kategorinya. */
    public const KIND_SINGLE = 'single';

    public const KIND_DOCUMENTATION = 'documentation';

    public const KIND_MEDIA_CENTER = 'media_center';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Relasi ke item Galeri.
     */
    public function galleries(): HasMany
    {
        return $this->hasMany(
            Gallery::class
        );
    }

    public function isDocumentation(): bool
    {
        return $this->slug === self::DOCUMENTATION_SLUG;
    }

    public function isMediaCenter(): bool
    {
        return $this->slug === self::MEDIA_CENTER_SLUG;
    }

    /**
     * single = foto tunggal (5 kategori reguler);
     * documentation / media_center = koleksi 1–5 GalleryImage.
     */
    public function kind(): string
    {
        return match (true) {
            $this->isDocumentation() => self::KIND_DOCUMENTATION,
            $this->isMediaCenter() => self::KIND_MEDIA_CENTER,
            default => self::KIND_SINGLE,
        };
    }

    public function usesCollection(): bool
    {
        return $this->kind() !== self::KIND_SINGLE;
    }
}
