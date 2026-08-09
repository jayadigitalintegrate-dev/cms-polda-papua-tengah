<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Foto Profil
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Gunakan foto profil untuk mengenali akun CMS yang sedang digunakan.
        </p>
    </header>

    <div class="mt-6 flex flex-col sm:flex-row sm:items-center gap-6">

        {{-- FOTO PROFIL SAAT INI --}}
        <div class="shrink-0">
            @if ($user->profile_photo_path)
                <img
                    src="{{ asset('storage/' . $user->profile_photo_path) }}"
                    alt="Foto profil {{ $user->name }}"
                    class="h-24 w-24 rounded-full object-cover border border-gray-200 shadow-sm"
                >
            @else
                <div
                    class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center border border-gray-300"
                >
                    <span class="text-2xl font-semibold text-gray-500">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                </div>
            @endif
        </div>

        <div class="flex-1">

            {{-- FORM UPLOAD FOTO --}}
            <form
                method="POST"
                action="{{ route('settings.profile-photo.update') }}"
                enctype="multipart/form-data"
            >
                @csrf

                <div>
                    <x-input-label
                        for="profile_photo"
                        :value="__('Pilih Foto Profil')"
                    />

                    <input
                        id="profile_photo"
                        name="profile_photo"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        class="mt-1 block w-full text-sm text-gray-700 border border-gray-300 rounded-md shadow-sm
                               file:mr-4 file:py-2 file:px-4
                               file:rounded-md file:border-0
                               file:text-sm file:font-semibold
                               file:bg-gray-100 file:text-gray-700
                               hover:file:bg-gray-200
                               focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >

                    <p class="mt-1 text-xs text-gray-500">
                        JPG, JPEG, PNG, atau WebP. Maksimal 2 MB.
                    </p>

                    <x-input-error
                        :messages="$errors->get('profile_photo')"
                        class="mt-2"
                    />
                </div>

                <div class="mt-4">
                    <x-primary-button>
                        {{ __('Ganti Foto') }}
                    </x-primary-button>
                </div>
            </form>

            {{-- HAPUS FOTO --}}
            @if ($user->profile_photo_path)
                <form
                    method="POST"
                    action="{{ route('settings.profile-photo.delete') }}"
                    class="mt-3"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="text-sm font-medium text-red-600 hover:text-red-800"
                        onclick="return confirm('Hapus foto profil ini?')"
                    >
                        Hapus Foto
                    </button>
                </form>
            @endif

        </div>
    </div>

    {{-- STATUS --}}
    @if (session('status') === 'profile-photo-updated')
        <p class="mt-4 text-sm font-medium text-green-600">
            Foto profil berhasil diperbarui.
        </p>
    @endif

    @if (session('status') === 'profile-photo-deleted')
        <p class="mt-4 text-sm font-medium text-green-600">
            Foto profil berhasil dihapus.
        </p>
    @endif
</section>