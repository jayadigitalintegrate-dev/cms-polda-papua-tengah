<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hero extends Model
{
    protected $fillable = [
        'image',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * Hero yang aktif untuk ditampilkan di website.
     */
    public function scopeActive($query)
    {
        return $query
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}