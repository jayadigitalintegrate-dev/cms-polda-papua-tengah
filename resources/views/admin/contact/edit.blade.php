<x-app-layout>

    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Kontak') }}
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Kelola informasi kontak dan media sosial Polda Papua Tengah.
            </p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700"
                >
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm font-semibold text-red-700">
                        Terdapat kesalahan pada data yang dimasukkan:
                    </p>

                    <ul class="mt-2 list-disc list-inside text-sm text-red-600 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- INFORMASI KONTAK --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-4xl">

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Informasi Kontak
                        </h3>

                        <p class="mt-1 text-sm text-gray-600">
                            Informasi ini akan digunakan pada halaman kontak website Polda Papua Tengah.
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('contact.update') }}"
                        class="space-y-6"
                    >
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div>
                                <label for="institution_name" class="block font-medium text-sm text-gray-700">
                                    Nama Instansi
                                </label>

                                <input
                                    id="institution_name"
                                    name="institution_name"
                                    type="text"
                                    value="{{ old('institution_name', $contact?->institution_name) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                    maxlength="255"
                                >

                                @error('institution_name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block font-medium text-sm text-gray-700">
                                    Nomor Telepon
                                </label>

                                <input
                                    id="phone"
                                    name="phone"
                                    type="text"
                                    value="{{ old('phone', $contact?->phone) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                    maxlength="30"
                                >

                                @error('phone')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block font-medium text-sm text-gray-700">
                                    Email
                                </label>

                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email', $contact?->email) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                    maxlength="255"
                                >

                                @error('email')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="service_hours" class="block font-medium text-sm text-gray-700">
                                    Jam Pelayanan
                                </label>

                                <input
                                    id="service_hours"
                                    name="service_hours"
                                    type="text"
                                    value="{{ old('service_hours', $contact?->service_hours) }}"
                                    placeholder="Contoh: Seninâ€“Jumat, 08.00â€“16.00 WIT"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                    maxlength="255"
                                >

                                @error('service_hours')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="call_center" class="block font-medium text-sm text-gray-700">
                                    Call Center
                                </label>

                                <input
                                    id="call_center"
                                    name="call_center"
                                    type="text"
                                    value="{{ old('call_center', $contact?->call_center) }}"
                                    placeholder="Contoh: 110"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                    maxlength="30"
                                >

                                @error('call_center')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="maps_url" class="block font-medium text-sm text-gray-700">
                                    Google Maps URL
                                </label>

                                <input
                                    id="maps_url"
                                    name="maps_url"
                                    type="url"
                                    value="{{ old('maps_url', $contact?->maps_url) }}"
                                    placeholder="https://maps.google.com/..."
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                    maxlength="2048"
                                >

                                @error('maps_url')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        <div>
                            <label for="address" class="block font-medium text-sm text-gray-700">
                                Alamat
                            </label>

                            <textarea
                                id="address"
                                name="address"
                                rows="4"
                                maxlength="5000"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500"
                            >{{ old('address', $contact?->address) }}</textarea>

                            @error('address')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- MEDIA SOSIAL --}}
                        <div class="pt-6 border-t border-gray-200">

                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Media Sosial
                                </h3>

                                <p class="mt-1 text-sm text-gray-600">
                                    Masukkan URL resmi akun media sosial Polda Papua Tengah.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <div>
                                    <label for="instagram_url" class="block font-medium text-sm text-gray-700">
                                        Instagram
                                    </label>

                                    <input
                                        id="instagram_url"
                                        name="instagram_url"
                                        type="url"
                                        value="{{ old('instagram_url', $contact?->instagram_url) }}"
                                        placeholder="https://www.instagram.com/..."
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm
                                               focus:border-indigo-500 focus:ring-indigo-500"
                                        maxlength="2048"
                                    >

                                    @error('instagram_url')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="facebook_url" class="block font-medium text-sm text-gray-700">
                                        Facebook
                                    </label>

                                    <input
                                        id="facebook_url"
                                        name="facebook_url"
                                        type="url"
                                        value="{{ old('facebook_url', $contact?->facebook_url) }}"
                                        placeholder="https://www.facebook.com/..."
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm
                                               focus:border-indigo-500 focus:ring-indigo-500"
                                        maxlength="2048"
                                    >

                                    @error('facebook_url')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="youtube_url" class="block font-medium text-sm text-gray-700">
                                        YouTube
                                    </label>

                                    <input
                                        id="youtube_url"
                                        name="youtube_url"
                                        type="url"
                                        value="{{ old('youtube_url', $contact?->youtube_url) }}"
                                        placeholder="https://www.youtube.com/..."
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm
                                               focus:border-indigo-500 focus:ring-indigo-500"
                                        maxlength="2048"
                                    >

                                    @error('youtube_url')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="tiktok_url" class="block font-medium text-sm text-gray-700">
                                        TikTok
                                    </label>

                                    <input
                                        id="tiktok_url"
                                        name="tiktok_url"
                                        type="url"
                                        value="{{ old('tiktok_url', $contact?->tiktok_url) }}"
                                        placeholder="https://www.tiktok.com/@..."
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm
                                               focus:border-indigo-500 focus:ring-indigo-500"
                                        maxlength="2048"
                                    >

                                    @error('tiktok_url')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="x_url" class="block font-medium text-sm text-gray-700">
                                        X / Twitter
                                    </label>

                                    <input
                                        id="x_url"
                                        name="x_url"
                                        type="url"
                                        value="{{ old('x_url', $contact?->x_url) }}"
                                        placeholder="https://x.com/..."
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm
                                               focus:border-indigo-500 focus:ring-indigo-500"
                                        maxlength="2048"
                                    >

                                    @error('x_url')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        <div class="flex items-center gap-3 pt-2">
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
                                Simpan Informasi Kontak
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

</x-app-layout>
