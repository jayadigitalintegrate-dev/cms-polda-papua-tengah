<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'category', 'slug');
    }
    public function newsCategory(): BelongsTo
{
    return $this->belongsTo(
        NewsCategory::class,
        'category',
        'slug'
    );
}
}