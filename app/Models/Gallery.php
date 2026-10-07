<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gallery extends Model
{
    /** Batas foto per koleksi (Galeri Dokumentasi & Media Center). */
    public const MAX_COLLECTION_IMAGES = 5;

    protected $fillable = [
        'gallery_category_id',
        'title',
        'slug',
        'description',
        'content',
        'image',
        'taken_at',
        'featured',
        'sort_order',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'taken_at' => 'date',
        'featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    public function galleryCategory(): BelongsTo
    {
        return $this->belongsTo(
            GalleryCategory::class,
            'gallery_category_id'
        );
    }

    /**
     * Foto child koleksi (Galeri Dokumentasi & Media Center).
     */
    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }
}
