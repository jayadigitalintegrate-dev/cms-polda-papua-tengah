<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class News extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'image',
        'document',
        'document_name',
        'category',
        'status',
        'featured',
        'sort_order',
        'author_id',
        'allow_comment',
        'show_author',
        'show_date',
        'last_modified_by',
        'published_at',
    ];

    /**
     * Relasi ke penulis berita.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Relasi ke kategori berita.
     * Kolom news.category menyimpan slug kategori.
     */
    public function newsCategory(): BelongsTo
    {
        return $this->belongsTo(
            NewsCategory::class,
            'category',
            'slug'
        );
    }

    /**
     * Relasi ke galeri foto berita.
     */
    public function images(): HasMany
    {
        return $this->hasMany(NewsImage::class)
            ->orderBy('sort_order');
    }

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'sort_order' => 'integer',
            'allow_comment' => 'boolean',
            'show_author' => 'boolean',
            'show_date' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
