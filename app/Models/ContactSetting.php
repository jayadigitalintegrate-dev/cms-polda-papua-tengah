<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSetting extends Model
{
    protected $fillable = [
        'institution_name',
        'address',
        'phone',
        'email',
        'service_hours',
        'call_center',
        'maps_url',
        'instagram_url',
        'facebook_url',
        'youtube_url',
        'tiktok_url',
        'x_url',
    ];
}