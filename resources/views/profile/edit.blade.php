<x-app-layout>

    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Profile') }}
            </h2>

            <p class="text-sm text-gray-500">
                Kelola foto, informasi akun, dan keamanan profile Anda.
            </p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- FOTO PROFIL --}}
            <div class="p-4 sm:p-8 bg-white shadow-sm sm:rounded-lg">
                <div class="max-w-xl">

                    @include('profile.partials.update-profile-photo-form', [
                        'user' => $user,
                    ])

                </div>
            </div>

            {{-- INFORMASI PROFILE --}}
            <div class="p-4 sm:p-8 bg-white shadow-sm sm:rounded-lg">
                <div class="max-w-xl">

                    @include('profile.partials.update-profile-information-form', [
                        'user' => $user,
                    ])

                </div>
            </div>

            {{-- PASSWORD --}}
            <div class="p-4 sm:p-8 bg-white shadow-sm sm:rounded-lg">
                <div class="max-w-xl">

                    @include('profile.partials.update-password-form', [
                        'user' => $user,
                    ])

                </div>
            </div>

            {{-- HAPUS AKUN --}}
            <div class="p-4 sm:p-8 bg-white shadow-sm sm:rounded-lg">
                <div class="max-w-xl">

                    @include('profile.partials.delete-user-form', [
                        'user' => $user,
                    ])

                </div>
            </div>

        </div>
    </div>

</x-app-layout>