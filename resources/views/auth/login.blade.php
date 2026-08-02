<x-guest-layout>
    <div class="w-full">

        {{-- Logo Polda Papua Tengah --}}
        <div class="flex justify-center mb-6">
            <img
                src="{{ asset('images/logo/logo-polda-papua-tengah.png') }}"
                alt="Logo Polda Papua Tengah"
                class="w-auto h-28 object-contain"
            >
        </div>

        {{-- Identitas CMS --}}
        <div class="text-center mb-7">
            <h1 class="text-2xl font-bold text-gray-800">
                CMS Polda Papua Tengah
            </h1>

            <p class="mt-2 text-sm text-gray-500">
                Sistem Manajemen Konten Website
            </p>

            <p class="mt-1 text-sm font-medium text-gray-600">
                Polda Papua Tengah
            </p>
        </div>

        {{-- Session Status --}}
        <x-auth-session-status
            class="mb-4"
            :status="session('status')"
        />

        {{-- Login Form --}}
        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <div>
                <x-input-label
                    for="email"
                    :value="__('Email')"
                />

                <x-text-input
                    id="email"
                    class="block mt-1 w-full"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Masukkan email"
                />

                <x-input-error
                    :messages="$errors->get('email')"
                    class="mt-2"
                />
            </div>

            {{-- Password --}}
            <div class="mt-5">
                <x-input-label
                    for="password"
                    :value="__('Password')"
                />

                <x-text-input
                    id="password"
                    class="block mt-1 w-full"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="Masukkan password"
                />

                <x-input-error
                    :messages="$errors->get('password')"
                    class="mt-2"
                />
            </div>

            {{-- Remember Me --}}
            <div class="block mt-5">
                <label
                    for="remember_me"
                    class="inline-flex items-center cursor-pointer"
                >
                    <input
                        id="remember_me"
                        type="checkbox"
                        class="rounded border-gray-300 text-green-600 shadow-sm focus:ring-green-500"
                        name="remember"
                    >

                    <span class="ms-2 text-sm text-gray-600">
                        {{ __('Ingat saya') }}
                    </span>
                </label>
            </div>

            {{-- Actions --}}
            <div class="mt-6">
                <x-primary-button
                    class="w-full justify-center py-3 bg-green-700 hover:bg-green-800 focus:bg-green-800 active:bg-green-900"
                >
                    {{ __('Masuk ke CMS') }}
                </x-primary-button>
            </div>

            {{-- Forgot Password --}}
            @if (Route::has('password.request'))
                <div class="text-center mt-5">
                    <a
                        class="text-sm text-gray-600 hover:text-green-700 underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 rounded-md"
                        href="{{ route('password.request') }}"
                    >
                        {{ __('Lupa password?') }}
                    </a>
                </div>
            @endif
        </form>

        {{-- Footer Login --}}
        <div class="text-center mt-8 pt-5 border-t border-gray-100">
            <p class="text-xs text-gray-400">
                &copy; {{ date('Y') }} Polda Papua Tengah
            </p>

            <p class="text-xs text-gray-400 mt-1">
                CMS Website Polda Papua Tengah
            </p>
        </div>

    </div>
</x-guest-layout>
```
