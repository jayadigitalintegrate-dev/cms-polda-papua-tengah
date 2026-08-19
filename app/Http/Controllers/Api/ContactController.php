<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;

class ContactController extends Controller
{
    /**
     * API publik informasi kontak Polda Papua Tengah.
     */
    public function index()
    {
        $contact = ContactSetting::first();

        if (!$contact) {
            return response()->json(null);
        }

        return response()->json([
            'institution_name' => $contact->institution_name,
            'address' => $contact->address,
            'phone' => $contact->phone,
            'email' => $contact->email,
            'service_hours' => $contact->service_hours,
            'call_center' => $contact->call_center,
            'maps_url' => $contact->maps_url,
            'instagram_url' => $contact->instagram_url,
            'facebook_url' => $contact->facebook_url,
            'youtube_url' => $contact->youtube_url,
            'tiktok_url' => $contact->tiktok_url,
            'x_url' => $contact->x_url,
        ]);
    }
}