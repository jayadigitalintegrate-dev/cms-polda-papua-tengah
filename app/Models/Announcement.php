<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'image',
        'attachment',
        'attachment_name',
        'priority',
        'type',
        'publish_start',
        'publish_end',
        'featured',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'publish_start' => 'datetime',
            'publish_end' => 'datetime',
            'featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}