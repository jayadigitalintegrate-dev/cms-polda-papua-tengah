<x-app-layout>

    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail User
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                Informasi akun pengguna CMS Polda Papua Tengah.
            </p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8">

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Informasi Akun
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Detail pengguna yang terdaftar pada CMS.
                        </p>
                    </div>

                    <div class="divide-y divide-gray-200 border border-gray-200 rounded-lg">

                        {{-- Nama --}}
                        <div class="px-5 py-4 sm:flex sm:items-center">
                            <div class="sm:w-1/3 text-sm font-medium text-gray-500">
                                Nama Lengkap
                            </div>

                            <div class="mt-1 sm:mt-0 sm:w-2/3 text-sm text-gray-900">
                                {{ $user->name }}
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="px-5 py-4 sm:flex sm:items-center">
                            <div class="sm:w-1/3 text-sm font-medium text-gray-500">
                                Email
                            </div>

                            <div class="mt-1 sm:mt-0 sm:w-2/3 text-sm text-gray-900">
                                {{ $user->email }}
                            </div>
                        </div>

                        {{-- Role --}}
                        <div class="px-5 py-4 sm:flex sm:items-center">
                            <div class="sm:w-1/3 text-sm font-medium text-gray-500">
                                Role
                            </div>

                            <div class="mt-1 sm:mt-0 sm:w-2/3">
                                @if ($user->role === 'superadmin')
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                        Superadmin
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                        Operator
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Dibuat --}}
                        <div class="px-5 py-4 sm:flex sm:items-center">
                            <div class="sm:w-1/3 text-sm font-medium text-gray-500">
                                Dibuat
                            </div>

                            <div class="mt-1 sm:mt-0 sm:w-2/3 text-sm text-gray-900">
                                {{ $user->created_at?->format('d/m/Y H:i') ?? '-' }}
                            </div>
                        </div>

                        {{-- Diperbarui --}}
                        <div class="px-5 py-4 sm:flex sm:items-center">
                            <div class="sm:w-1/3 text-sm font-medium text-gray-500">
                                Terakhir Diperbarui
                            </div>

                            <div class="mt-1 sm:mt-0 sm:w-2/3 text-sm text-gray-900">
                                {{ $user->updated_at?->format('d/m/Y H:i') ?? '-' }}
                            </div>
                        </div>

                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-between gap-3 pt-6">

                        <a
                            href="{{ route('users.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200"
                        >
                            Kembali
                        </a>

                        @if ($user->role !== 'superadmin')
                            <a
                                href="{{ route('users.edit', $user) }}"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                            >
                                Edit User
                            </a>
                        @endif

                    </div>

                </div>
            </div>

        </div>
    </div>

</x-app-layout>
