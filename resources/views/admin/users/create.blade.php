<x-app-layout>

    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create New User
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                Buat akun Operator atau Supervisi CMS Polda Papua Tengah.
            </p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4">
                    <div class="font-semibold text-red-800 mb-2">
                        Periksa kembali data berikut:
                    </div>

                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8">

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Informasi Akun
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Akun hanya dapat dibuat oleh Super Admin.
                            Pilih role sesuai tugas pengguna.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
                        @csrf

                        {{-- Nama --}}
                        <div>
                            <x-input-label for="name" :value="__('Nama Lengkap')" />

                            <x-text-input
                                id="name"
                                name="name"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('name')"
                                required
                                autofocus
                                autocomplete="name"
                                maxlength="255"
                            />

                            <x-input-error
                                :messages="$errors->get('name')"
                                class="mt-2"
                            />
                        </div>

                        {{-- Email --}}
                        <div>
                            <x-input-label for="email" :value="__('Email')" />

                            <x-text-input
                                id="email"
                                name="email"
                                type="email"
                                class="mt-1 block w-full"
                                :value="old('email')"
                                required
                                autocomplete="username"
                                maxlength="255"
                            />

                            <x-input-error
                                :messages="$errors->get('email')"
                                class="mt-2"
                            />
                        </div>

                        {{-- Role --}}
                        <div>
                            <x-input-label for="role" :value="__('Role Pengguna')" />

                            <select
                                id="role"
                                name="role"
                                required
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="">-- Pilih Role --</option>
                                <option value="operator" @selected(old('role') === 'operator')>
                                    Operator
                                </option>
                                <option value="supervisi" @selected(old('role') === 'supervisi')>
                                    Supervisi
                                </option>
                            </select>

                            <p class="mt-1 text-xs text-gray-500">
                                Operator untuk pelaksanaan pekerjaan CMS.
                                Supervisi untuk pengawasan dan monitoring.
                            </p>

                            <x-input-error
                                :messages="$errors->get('role')"
                                class="mt-2"
                            />
                        </div>
{{-- Password --}}
<div
    x-data="{
        show: false,
        password: '',

        get score() {
            let score = 0;

            if (this.password.length >= 8) score++;
            if (/[a-z]/.test(this.password)) score++;
            if (/[A-Z]/.test(this.password)) score++;
            if (/[0-9]/.test(this.password)) score++;
            if (/[^A-Za-z0-9]/.test(this.password)) score++;

            return score;
        },

        get label() {
            if (!this.password) return '';
            if (this.score <= 2) return 'Password lemah';
            if (this.score <= 3) return 'Password sedang';
            return 'Password kuat';
        },

        get barClass() {
            if (!this.password) return 'bg-gray-200';
            if (this.score <= 2) return 'bg-red-500';
            if (this.score <= 3) return 'bg-yellow-500';
            return 'bg-green-500';
        },

        get textClass() {
            if (!this.password) return 'text-gray-500';
            if (this.score <= 2) return 'text-red-600';
            if (this.score <= 3) return 'text-yellow-600';
            return 'text-green-600';
        }
    }"
>
    <x-input-label for="password" :value="__('Password')" />

    <div class="relative mt-1">
        <x-text-input
            id="password"
            name="password"
            x-model="password"
            x-bind:type="show ? 'text' : 'password'"
            class="block w-full pe-12"
            required
            autocomplete="new-password"
        />

        <button
            type="button"
            @click="show = !show"
            class="absolute inset-y-0 end-0 flex items-center px-4 text-gray-500 hover:text-gray-700"
            :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
        >
            {{-- Mata tertutup --}}
            <svg
                x-show="!show"
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                />
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
            </svg>

            {{-- Mata terbuka --}}
            <svg
                x-show="show"
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 3l18 18"
                />
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M10.584 10.587a2 2 0 002.829 2.829"
                />
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9.88 5.09A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.02 10.02 0 01-4.043 5.033M6.228 6.228A10.01 10.01 0 002.458 12C3.732 16.057 7.523 19 12 19c1.61 0 3.12-.38 4.455-1.053"
                />
            </svg>
        </button>
    </div>

    {{-- Password Strength --}}
    <div class="mt-2">
        <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-200">
            <div
                class="h-full rounded-full transition-all duration-300"
                :class="barClass"
                :style="`width: ${password ? (score / 5) * 100 : 0}%`"
            ></div>
        </div>

        <p
            x-show="password"
            x-cloak
            class="mt-1 text-xs font-medium"
            :class="textClass"
            x-text="label"
        ></p>
    </div>

    <p class="mt-1 text-xs text-gray-500">
        Gunakan minimal 8 karakter dengan kombinasi huruf besar, huruf kecil, angka, dan simbol.
    </p>

    <x-input-error
        :messages="$errors->get('password')"
        class="mt-2"
    />
</div>

{{-- Konfirmasi Password --}}
<div x-data="{ show: false }">
    <x-input-label
        for="password_confirmation"
        :value="__('Konfirmasi Password')"
    />

    <div class="relative mt-1">
        <x-text-input
            id="password_confirmation"
            name="password_confirmation"
            x-bind:type="show ? 'text' : 'password'"
            class="block w-full pe-12"
            required
            autocomplete="new-password"
        />

        <button
            type="button"
            @click="show = !show"
            class="absolute inset-y-0 end-0 flex items-center px-4 text-gray-500 hover:text-gray-700"
            :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
        >
            <svg
                x-show="!show"
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                />
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
            </svg>

            <svg
                x-show="show"
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 3l18 18"
                />
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M10.584 10.587a2 2 0 001.829 2.829"
                />
            </svg>
        </button>
    </div>

    <x-input-error
        :messages="$errors->get('password_confirmation')"
        class="mt-2"
    />
</div>

                        {{-- Actions --}}
                        <div class="flex items-center justify-between gap-3 pt-4 border-t border-gray-200">

                            <a
                                href="{{ route('users.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200"
                            >
                                Batal
                            </a>

                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Simpan User
                            </button>

                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>

</x-app-layout>
