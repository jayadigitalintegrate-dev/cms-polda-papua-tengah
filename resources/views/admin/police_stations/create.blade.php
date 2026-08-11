<x-app-layout>

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Tambah Polres
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Tambahkan data Jajaran Polres Wilayah Hukum Papua Tengah.
                </p>
            </div>

            <a
                href="{{ route('police-stations.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
            >
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                    <p class="font-semibold text-red-800">
                        Terdapat kesalahan pada data:
                    </p>

                    <ul class="mt-2 list-disc pl-5 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('police-stations.store') }}"
                enctype="multipart/form-data"
                class="space-y-6"
            >
                @csrf

                {{-- IDENTITAS POLRES --}}
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-5">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Identitas Polres
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Informasi utama Kepolisian Resor.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 px-6 py-6 md:grid-cols-2">

                        {{-- NAMA POLRES --}}
                        <div>
                            <label for="name_id" class="block text-sm font-medium text-gray-700">
                                Nama Polres
                            </label>

                            <input
                                id="name_id"
                                name="name_id"
                                type="text"
                                value="{{ old('name_id') }}"
                                required
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Contoh: Polres Nabire"
                            >

                            @error('name_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- NAMA INGGRIS --}}
                        <div>
                            <label for="name_en" class="block text-sm font-medium text-gray-700">
                                Nama Bahasa Inggris
                            </label>

                            <input
                                id="name_en"
                                name="name_en"
                                type="text"
                                value="{{ old('name_en') }}"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Contoh: Nabire Resort Police"
                            >

                            @error('name_en')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- KAPOLRES --}}
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-5">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Kapolres
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Informasi Kepala Kepolisian Resor.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 px-6 py-6 md:grid-cols-2">

                        {{-- NAMA KAPOLRES --}}
                        <div>
                            <label for="chief_name" class="block text-sm font-medium text-gray-700">
                                Nama Kapolres
                            </label>

                            <input
                                id="chief_name"
                                name="chief_name"
                                type="text"
                                value="{{ old('chief_name') }}"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Nama lengkap Kapolres"
                            >

                            @error('chief_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- PANGKAT --}}
                        <div>
                            <label for="chief_rank" class="block text-sm font-medium text-gray-700">
                                Pangkat Kapolres
                            </label>

                            <input
                                id="chief_rank"
                                name="chief_rank"
                                type="text"
                                value="{{ old('chief_rank') }}"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Contoh: AKBP"
                            >

                            @error('chief_rank')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- NRP --}}
                        <div>
                            <label for="chief_nrp" class="block text-sm font-medium text-gray-700">
                                NRP Kapolres
                            </label>

                            <input
                                id="chief_nrp"
                                name="chief_nrp"
                                type="text"
                                value="{{ old('chief_nrp') }}"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="NRP"
                            >

                            @error('chief_nrp')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- FOTO --}}
                        <div>
                            <label for="chief_photo" class="block text-sm font-medium text-gray-700">
                                Foto Kapolres
                            </label>

                            <input
                                id="chief_photo"
                                name="chief_photo"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp"
                                class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                JPG, JPEG, PNG atau WebP. Maksimal 5 MB.
                            </p>

                            @error('chief_photo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- WILAYAH DAN KONTAK --}}
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-5">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Wilayah dan Kontak
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Informasi wilayah hukum dan kontak resmi Polres.
                        </p>
                    </div>

                    <div class="space-y-6 px-6 py-6">

                        {{-- WILAYAH HUKUM --}}
                        <div>
                            <label for="jurisdiction" class="block text-sm font-medium text-gray-700">
                                Wilayah Hukum
                            </label>

                            <textarea
                                id="jurisdiction"
                                name="jurisdiction"
                                rows="3"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Wilayah hukum Polres"
                            >{{ old('jurisdiction') }}</textarea>

                            @error('jurisdiction')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- ALAMAT --}}
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">
                                Alamat
                            </label>

                            <textarea
                                id="address"
                                name="address"
                                rows="3"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Alamat lengkap kantor Polres"
                            >{{ old('address') }}</textarea>

                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                            {{-- TELEPON --}}
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">
                                    Telepon
                                </label>

                                <input
                                    id="phone"
                                    name="phone"
                                    type="text"
                                    value="{{ old('phone') }}"
                                    class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Nomor telepon"
                                >

                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- EMAIL --}}
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">
                                    Email
                                </label>

                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Email resmi Polres"
                                >

                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        {{-- WEBSITE --}}
                        <div>
                            <label for="website" class="block text-sm font-medium text-gray-700">
                                Website
                            </label>

                            <input
                                id="website"
                                name="website"
                                type="text"
                                value="{{ old('website') }}"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="https://..."
                            >

                            @error('website')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- INFORMASI TAMBAHAN --}}
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-5">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Informasi Tambahan
                        </h3>
                    </div>

                    <div class="px-6 py-6">
                        <label for="description" class="block text-sm font-medium text-gray-700">
                            Deskripsi
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            rows="5"
                            class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Deskripsi singkat tentang Polres"
                        >{{ old('description') }}</textarea>

                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- PENGATURAN --}}
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-5">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Pengaturan
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 gap-6 px-6 py-6 md:grid-cols-2">

                        {{-- URUTAN --}}
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700">
                                Urutan Tampil
                            </label>

                            <input
                                id="sort_order"
                                name="sort_order"
                                type="number"
                                min="0"
                                value="{{ old('sort_order', 0) }}"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            @error('sort_order')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- STATUS --}}
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">
                                Status
                            </label>

                            <select
                                id="status"
                                name="status"
                                required
                                class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="active" @selected(old('status', 'active') === 'active')>
                                    Aktif
                                </option>

                                <option value="inactive" @selected(old('status') === 'inactive')>
                                    Tidak Aktif
                                </option>
                            </select>

                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- ACTION --}}
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                    <a
                        href="{{ route('police-stations.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Simpan Polres
                    </button>

                </div>

            </form>

        </div>
    </div>

</x-app-layout>
