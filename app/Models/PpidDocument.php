<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PpidDocument extends Model
{
    protected $fillable = [

        'ppid_category_id',

        'title',
        'slug',

        'summary',
        'content',

        'document_number',

        'document',
        'document_name',

        'thumbnail',

        'publication_year',

        'sort_order',

        'status',

        'published_at',

        'download_count',

        'view_count',

        'created_by',

        'updated_by',

    ];

    protected $casts = [

        'published_at' => 'datetime',

        'publication_year' => 'integer',

        'download_count' => 'integer',

        'view_count' => 'integer',

        'sort_order' => 'integer',

    ];

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */

    public function getDocumentUrlAttribute(): ?string
    {
        if (!$this->document) {
            return null;
        }

        return Storage::url(
            $this->document
        );
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail) {
            return null;
        }

        return Storage::url(
            $this->thumbnail
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            PpidCategory::class,
            'ppid_category_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }
}