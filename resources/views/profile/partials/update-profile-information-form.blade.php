<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Informasi Profile
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Perbarui nama dan alamat email akun CMS Anda.
        </p>
    </header>

    <form
        method="POST"
        action="{{ route('profile.update') }}"
        class="mt-6 space-y-6"
    >
        @csrf
        @method('PATCH')

        {{-- NAMA --}}
        <div>
            <x-input-label
                for="name"
                :value="__('Nama')"
            />

            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full"
                :value="old('name', $user->name)"
                required
                autofocus
                autocomplete="name"
            />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('name')"
            />
        </div>

        {{-- EMAIL --}}
        <div>
            <x-input-label
                for="email"
                :value="__('Email')"
            />

            <x-text-input
                id="email"
                name="email"
                type="email"
                class="mt-1 block w-full"
                :value="old('email', $user->email)"
                required
                autocomplete="username"
            />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('email')"
            />

            {{-- VERIFIKASI EMAIL --}}
            @if (
                $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail
                && ! $user->hasVerifiedEmail()
            )
                <div class="mt-2">
                    <p class="text-sm text-gray-800">
                        Email Anda belum diverifikasi.

                        <button
                            form="send-verification"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md
                                   focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Kirim ulang email verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-green-600">
                            Link verifikasi baru telah dikirim ke email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- BUTTON --}}
        <div class="flex items-center gap-4">
            <x-primary-button>
                {{ __('Simpan Perubahan') }}
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600 font-medium"
                >
                    Pengaturan profile berhasil disimpan.
                </p>
            @endif
        </div>
    </form>

    {{-- FORM VERIFIKASI EMAIL --}}
    <form
        id="send-verification"
        method="POST"
        action="{{ route('verification.send') }}"
        class="hidden"
    >
        @csrf
    </form>
</section>