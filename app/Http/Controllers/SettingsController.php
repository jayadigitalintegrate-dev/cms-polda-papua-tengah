<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Menampilkan halaman Settings.
     */
    public function edit(Request $request): View
    {
        $setting = SiteSetting::first();

        return view('admin.settings.edit', [
            'user' => $request->user(),
            'setting' => $setting,
        ]);
    }

    /**
     * Upload / mengganti foto profil akun yang sedang login.
     */
    public function updateProfilePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'profile_photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $user = $request->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $path = $request->file('profile_photo')
            ->store('profile-photos', 'public');

        $user->profile_photo_path = $path;
        $user->save();

        return redirect()
            ->route('settings.edit')
            ->with('status', 'profile-photo-updated');
    }

    /**
     * Menghapus foto profil akun yang sedang login.
     */
    public function deleteProfilePhoto(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);

            $user->profile_photo_path = null;
            $user->save();
        }

        return redirect()
            ->route('settings.edit')
            ->with('status', 'profile-photo-deleted');
    }

    /**
     * Upload / mengganti logo Polda.
     *
     * HANYA SUPER ADMIN.
     */
    public function updateLogo(Request $request): RedirectResponse
    {
        abort_unless(
            $request->user()?->role === 'superadmin',
            403
        );

        $request->validate([
            'logo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ]);

        $setting = SiteSetting::first();

        if (!$setting) {
            $setting = new SiteSetting();
        }

        if ($setting->logo_path) {
            Storage::disk('public')->delete($setting->logo_path);
        }

        $path = $request->file('logo')
            ->store('site-settings', 'public');

        $setting->logo_path = $path;
        $setting->save();

        return redirect()
            ->route('settings.edit')
            ->with('status', 'logo-updated');
    }

    /**
     * Menghapus logo Polda.
     *
     * HANYA SUPER ADMIN.
     */
    public function deleteLogo(Request $request): RedirectResponse
    {
        abort_unless(
            $request->user()?->role === 'superadmin',
            403
        );

        $setting = SiteSetting::first();

        if ($setting?->logo_path) {
            Storage::disk('public')->delete($setting->logo_path);

            $setting->logo_path = null;
            $setting->save();
        }

        return redirect()
            ->route('settings.edit')
            ->with('status', 'logo-deleted');
    }
}