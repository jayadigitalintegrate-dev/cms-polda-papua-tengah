<?php

namespace App\Http\Controllers;

use App\Models\ContactSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactSettingController extends Controller
{
    public function edit(): View
    {
        $contact = ContactSetting::first();

        return view('admin.contact.edit', compact('contact'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'institution_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:5000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'service_hours' => ['nullable', 'string', 'max:255'],
            'call_center' => ['nullable', 'string', 'max:30'],
            'maps_url' => ['nullable', 'url', 'max:2048'],
            'instagram_url' => ['nullable', 'url', 'max:2048'],
            'facebook_url' => ['nullable', 'url', 'max:2048'],
            'youtube_url' => ['nullable', 'url', 'max:2048'],
            'tiktok_url' => ['nullable', 'url', 'max:2048'],
            'x_url' => ['nullable', 'url', 'max:2048'],
        ]);

        ContactSetting::updateOrCreate(
            ['id' => 1],
            $validated
        );

        return redirect()
            ->route('contact.edit')
            ->with('success', 'Informasi kontak berhasil diperbarui.');
    }
}
