<x-app-layout>

    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit User
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                Kelola akun Operator CMS Polda Papua Tengah.
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
                            Perbarui data akun operator. Kosongkan password jika tidak ingin menggantinya.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        {{-- Nama --}}
                        <div>
                            <x-input-label for="name" :value="__('Nama Lengkap')" />

                            <x-text-input
                                id="name"
                                name="name"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('name', $user->name)"
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
                                :value="old('email', $user->email)"
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

                            <x-text-input
                                id="role"
                                type="text"
                                class="mt-1 block w-full bg-gray-50"
                                value="Operator"
                                disabled
                            />

                            <p class="mt-1 text-xs text-gray-500">
                                Role operator dipertahankan dan tidak dapat diubah dari halaman edit.
                            </p>
                        </div>

                        {{-- Password --}}
                        <div>
                            <x-input-label for="password" :value="__('Password Baru')" />

                            <x-text-input
                                id="password"
                                name="password"
                                type="password"
                                class="mt-1 block w-full"
                                autocomplete="new-password"
                            />

                            <p class="mt-1 text-xs text-gray-500">
                                Kosongkan jika password tidak ingin diubah.
                            </p>

                            <x-input-error
                                :messages="$errors->get('password')"
                                class="mt-2"
                            />
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div>
                            <x-input-label
                                for="password_confirmation"
                                :value="__('Konfirmasi Password Baru')"
                            />

                            <x-text-input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                class="mt-1 block w-full"
                                autocomplete="new-password"
                            />

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
                                Simpan Perubahan
                            </button>

                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

</x-app-layout>
