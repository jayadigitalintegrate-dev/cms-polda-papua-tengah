<x-app-layout>

    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Settings') }}
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Kelola foto profil akun dan pengaturan logo CMS.
            </p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ========================================================= --}}
            {{-- FOTO PROFIL AKUN --}}
            {{-- ========================================================= --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Foto Profil
                        </h3>

                        <p class="mt-1 text-sm text-gray-600">
                            Foto ini digunakan sebagai foto profil akun Anda
                            dan akan ditampilkan pada navigator CMS.
                        </p>
                    </div>

                    {{-- Preview Foto --}}
                    <div class="flex items-center gap-5 mb-6">

                        @if ($user->profile_photo_path)

                            <img
                                src="{{ Storage::url($user->profile_photo_path) }}"
                                alt="Foto Profil {{ $user->name }}"
                                class="w-20 h-20 rounded-full object-cover border-2 border-gray-200 shadow-sm"
                            >

                        @else

                            <div
                                class="w-20 h-20 rounded-full bg-gray-200 border-2 border-gray-300 flex items-center justify-center"
                            >
                                <span class="text-2xl font-semibold text-gray-500">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                            </div>

                        @endif

                        <div>
                            <p class="font-medium text-gray-900">
                                {{ $user->name }}
                            </p>

                            <p class="text-sm text-gray-500">
                                {{ $user->email }}
                            </p>

                            <p class="mt-1 text-xs text-gray-400">
                                {{ ucfirst($user->role) }}
                            </p>
                        </div>

                    </div>

                    {{-- Upload Foto Profil --}}
                    <form
                        method="POST"
                        action="{{ route('settings.profile-photo.update') }}"
                        enctype="multipart/form-data"
                        class="space-y-4"
                    >
                        @csrf

                        <div>
                            <label
                                for="profile_photo"
                                class="block font-medium text-sm text-gray-700"
                            >
                                Pilih Foto Profil
                            </label>

                            <input
                                id="profile_photo"
                                name="profile_photo"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                class="mt-1 block w-full text-sm text-gray-700
                                       border border-gray-300 rounded-md
                                       file:mr-4 file:py-2 file:px-4
                                       file:rounded-md file:border-0
                                       file:text-sm file:font-semibold
                                       file:bg-gray-100 file:text-gray-700
                                       hover:file:bg-gray-200"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Format JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
                            </p>

                            @error('profile_photo')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-3">

                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2
                                       bg-gray-800 border border-transparent
                                       rounded-md font-semibold text-xs
                                       text-white uppercase tracking-widest
                                       hover:bg-gray-700
                                       focus:bg-gray-700
                                       active:bg-gray-900
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-indigo-500
                                       focus:ring-offset-2"
                            >
                                Simpan Foto Profil
                            </button>

                        </div>

                    </form>

                    {{-- Hapus Foto Profil --}}
                    @if ($user->profile_photo_path)

                        <form
                            method="POST"
                            action="{{ route('settings.profile-photo.delete') }}"
                            class="mt-3"
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto profil?');"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2
                                       bg-white border border-red-300
                                       rounded-md font-semibold text-xs
                                       text-red-600 uppercase tracking-widest
                                       hover:bg-red-50
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-red-500
                                       focus:ring-offset-2"
                            >
                                Hapus Foto Profil
                            </button>
                        </form>

                    @endif

                </div>
            </div>


            {{-- ========================================================= --}}
            {{-- LOGO POLDA - SUPER ADMIN SAJA --}}
            {{-- ========================================================= --}}
            @if ($user->role === 'superadmin')

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">

                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Logo Polda Papua Tengah
                            </h3>

                            <p class="mt-1 text-sm text-gray-600">
                                Logo ini digunakan pada halaman Login dan
                                Dashboard CMS.
                            </p>

                            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                <p class="text-sm text-yellow-800">
                                    Pengaturan logo hanya dapat dilakukan oleh
                                    <strong>Super Admin</strong>.
                                </p>
                            </div>
                        </div>

                        {{-- Preview Logo --}}
                        <div class="mb-6">

                            <p class="block font-medium text-sm text-gray-700 mb-2">
                                Logo Saat Ini
                            </p>

                            @if ($setting?->logo_path)

                                <div class="inline-flex items-center justify-center
                                            p-4 bg-gray-50 border border-gray-200
                                            rounded-lg">

                                    <img
                                        src="{{ Storage::url($setting->logo_path) }}"
                                        alt="Logo Polda Papua Tengah"
                                        class="max-w-xs h-32 object-contain"
                                    >

                                </div>

                            @else

                                <div class="p-6 bg-gray-50 border border-gray-200 rounded-lg">
                                    <p class="text-sm text-gray-500">
                                        Logo belum diatur.
                                    </p>
                                </div>

                            @endif

                        </div>

                        {{-- Upload Logo --}}
                        <form
                            method="POST"
                            action="{{ route('settings.logo.update') }}"
                            enctype="multipart/form-data"
                            class="space-y-4"
                        >
                            @csrf

                            <div>
                                <label
                                    for="logo"
                                    class="block font-medium text-sm text-gray-700"
                                >
                                    Pilih Logo Polda
                                </label>

                                <input
                                    id="logo"
                                    name="logo"
                                    type="file"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                    class="mt-1 block w-full text-sm text-gray-700
                                           border border-gray-300 rounded-md
                                           file:mr-4 file:py-2 file:px-4
                                           file:rounded-md file:border-0
                                           file:text-sm file:font-semibold
                                           file:bg-gray-100 file:text-gray-700
                                           hover:file:bg-gray-200"
                                >

                                <p class="mt-1 text-xs text-gray-500">
                                    Format JPG, JPEG, PNG, atau WEBP. Maksimal 4 MB.
                                </p>

                                @error('logo')
                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="flex items-center gap-3">

                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2
                                           bg-gray-800 border border-transparent
                                           rounded-md font-semibold text-xs
                                           text-white uppercase tracking-widest
                                           hover:bg-gray-700
                                           focus:bg-gray-700
                                           active:bg-gray-900
                                           focus:outline-none
                                           focus:ring-2
                                           focus:ring-indigo-500
                                           focus:ring-offset-2"
                                >
                                    Simpan Logo Polda
                                </button>

                            </div>

                        </form>

                        {{-- Hapus Logo --}}
                        @if ($setting?->logo_path)

                            <form
                                method="POST"
                                action="{{ route('settings.logo.delete') }}"
                                class="mt-3"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus logo Polda?');"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2
                                           bg-white border border-red-300
                                           rounded-md font-semibold text-xs
                                           text-red-600 uppercase tracking-widest
                                           hover:bg-red-50
                                           focus:outline-none
                                           focus:ring-2
                                           focus:ring-red-500
                                           focus:ring-offset-2"
                                >
                                    Hapus Logo Polda
                                </button>

                            </form>

                        @endif

                    </div>
                </div>

            @endif


            {{-- ========================================================= --}}
            {{-- NOTIFIKASI --}}
            {{-- ========================================================= --}}
            @if (session('status'))

                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="p-4 bg-green-50 border border-green-200
                           rounded-lg text-sm text-green-700"
                >

                    @switch(session('status'))

                        @case('profile-photo-updated')
                            Foto profil berhasil diperbarui.
                            @break

                        @case('profile-photo-deleted')
                            Foto profil berhasil dihapus.
                            @break

                        @case('logo-updated')
                            Logo Polda berhasil diperbarui.
                            @break

                        @case('logo-deleted')
                            Logo Polda berhasil dihapus.
                            @break

                        @default
                            Pengaturan berhasil diperbarui.

                    @endswitch

                </div>

            @endif

        </div>
    </div>

</x-app-layout>