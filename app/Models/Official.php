<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Official extends Model
{
    protected $fillable = [
        'photo',
        'name_id',
        'name_en',
        'rank',
        'position_id',
        'position_ref_id',
        'position_en',
        'nrp',
        'birth_place',
        'birth_date',
        'religion',
        'marital_status',
        'spouse',
        'children',
        'motto',
        'education',
        'assignments',
        'career',
        'awards',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'education' => 'array',
            'assignments' => 'array',
            'career' => 'array',
            'awards' => 'array',
            'children' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_ref_id');
    }
}
