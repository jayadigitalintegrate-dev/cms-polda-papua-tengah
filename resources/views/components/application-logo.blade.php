@php
    use App\Models\SiteSetting;

    $siteSetting = SiteSetting::first();
@endphp

<div class="flex items-center gap-3 leading-tight">

    {{-- Logo Polda --}}
    @if ($siteSetting?->logo_path)
        <img
            src="{{ Storage::url($siteSetting->logo_path) }}"
            alt="Logo Polda Papua Tengah"
            class="h-10 w-auto object-contain"
        >
    @else
        <img
            src="{{ asset('images/logo/logo-polda-papua-tengah.png') }}"
            alt="Logo Polda Papua Tengah"
            class="h-10 w-auto object-contain"
        >
    @endif

    {{-- Identitas CMS --}}
    <div class="hidden sm:block">
        <div class="font-bold text-gray-800">
            CMS Polda Papua Tengah
        </div>

        <div class="text-xs text-gray-500">
            Content Management System
        </div>
    </div>

</div>