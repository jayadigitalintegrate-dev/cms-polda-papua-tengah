<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoliceStation extends Model
{
    protected $fillable = [
        'name_id',
        'name_en',
        'chief_name',
        'chief_rank',
        'chief_nrp',
        'chief_photo',
        'jurisdiction',
        'address',
        'phone',
        'email',
        'website',
        'description',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }
}