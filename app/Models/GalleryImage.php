<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryImage extends Model
{
    protected $fillable = [
        'gallery_id',
        'image',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Koleksi Galeri induk.
     */
    public function gallery(): BelongsTo
    {
        return $this->belongsTo(
            Gallery::class,
            'gallery_id'
        );
    }
}
