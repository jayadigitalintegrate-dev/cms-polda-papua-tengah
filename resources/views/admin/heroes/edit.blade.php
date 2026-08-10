<x-app-layout>

    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Hero
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui konten dan pengaturan Hero website Polda Papua Tengah.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                    <p class="font-semibold text-sm text-red-700">
                        Periksa kembali data yang dimasukkan.
                    </p>

                    <ul class="mt-2 list-disc list-inside text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">

                    <form
                        method="POST"
                        action="{{ route('heroes.update', $hero) }}"
                        enctype="multipart/form-data"
                        class="space-y-6"
                    >
                        @csrf
                        @method('PUT')

                        {{-- CURRENT IMAGE --}}
                        <div>
                            <x-input-label :value="__('Gambar Hero Saat Ini')" />

                            <div class="mt-2 overflow-hidden rounded-xl border border-gray-200 bg-gray-100">
                                <img
                                    src="{{ asset('storage/' . $hero->image) }}"
                                    alt="{{ $hero->title }}"
                                    class="w-full max-h-72 object-cover"
                                >
                            </div>
                        </div>

                        {{-- TITLE --}}
                        <div>
                            <x-input-label
                                for="title"
                                :value="__('Judul Hero')"
                            />

                            <x-text-input
                                id="title"
                                name="title"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('title', $hero->title)"
                                required
                                autofocus
                            />

                            <x-input-error
                                :messages="$errors->get('title')"
                                class="mt-2"
                            />
                        </div>

                        {{-- SUBTITLE --}}
                        <div>
                            <x-input-label
                                for="subtitle"
                                :value="__('Subjudul / Deskripsi')"
                            />

                            <textarea
                                id="subtitle"
                                name="subtitle"
                                rows="4"
                                class="mt-1 block w-full border-gray-300
                                       focus:border-indigo-500 focus:ring-indigo-500
                                       rounded-md shadow-sm"
                            >{{ old('subtitle', $hero->subtitle) }}</textarea>

                            <x-input-error
                                :messages="$errors->get('subtitle')"
                                class="mt-2"
                            />
                        </div>

                        {{-- NEW IMAGE --}}
                        <div>
                            <x-input-label
                                for="image"
                                :value="__('Ganti Gambar Hero')"
                            />

                            <input
                                id="image"
                                name="image"
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
                                Kosongkan jika tidak ingin mengganti gambar.
                                JPG, JPEG, PNG, atau WebP. Maksimal 5 MB.
                            </p>

                            <x-input-error
                                :messages="$errors->get('image')"
                                class="mt-2"
                            />
                        </div>

                        {{-- CTA --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div>
                                <x-input-label
                                    for="button_text"
                                    :value="__('Teks Tombol CTA')"
                                />

                                <x-text-input
                                    id="button_text"
                                    name="button_text"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :value="old('button_text', $hero->button_text)"
                                />

                                <x-input-error
                                    :messages="$errors->get('button_text')"
                                    class="mt-2"
                                />
                            </div>

                            <div>
                                <x-input-label
                                    for="button_url"
                                    :value="__('URL Tombol CTA')"
                                />

                                <x-text-input
                                    id="button_url"
                                    name="button_url"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :value="old('button_url', $hero->button_url)"
                                />

                                <x-input-error
                                    :messages="$errors->get('button_url')"
                                    class="mt-2"
                                />
                            </div>

                        </div>

                        {{-- STATUS + SORT --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div>
                                <x-input-label
                                    for="status"
                                    :value="__('Status')"
                                />

                                <select
                                    id="status"
                                    name="status"
                                    class="mt-1 block w-full border-gray-300
                                           focus:border-indigo-500 focus:ring-indigo-500
                                           rounded-md shadow-sm"
                                    required
                                >
                                    <option
                                        value="active"
                                        @selected(old('status', $hero->status) === 'active')
                                    >
                                        Aktif
                                    </option>

                                    <option
                                        value="inactive"
                                        @selected(old('status', $hero->status) === 'inactive')
                                    >
                                        Nonaktif
                                    </option>
                                </select>

                                <x-input-error
                                    :messages="$errors->get('status')"
                                    class="mt-2"
                                />
                            </div>

                            <div>
                                <x-input-label
                                    for="sort_order"
                                    :value="__('Urutan')"
                                />

                                <x-text-input
                                    id="sort_order"
                                    name="sort_order"
                                    type="number"
                                    min="0"
                                    class="mt-1 block w-full"
                                    :value="old('sort_order', $hero->sort_order)"
                                    required
                                />

                                <p class="mt-1 text-xs text-gray-500">
                                    Angka lebih kecil tampil lebih dahulu.
                                </p>

                                <x-input-error
                                    :messages="$errors->get('sort_order')"
                                    class="mt-2"
                                />
                            </div>

                        </div>

                        {{-- PUBLISHED AT --}}
                        <div>
                            <x-input-label
                                for="published_at"
                                :value="__('Waktu Publikasi')"
                            />

                            <x-text-input
                                id="published_at"
                                name="published_at"
                                type="datetime-local"
                                class="mt-1 block w-full"
                                :value="old(
                                    'published_at',
                                    $hero->published_at
                                        ? $hero->published_at->format('Y-m-d\TH:i')
                                        : ''
                                )"
                            />

                            <p class="mt-1 text-xs text-gray-500">
                                Kosongkan jika Hero aktif dapat langsung ditampilkan.
                            </p>

                            <x-input-error
                                :messages="$errors->get('published_at')"
                                class="mt-2"
                            />
                        </div>

                        {{-- BUTTONS --}}
                        <div class="flex flex-col-reverse sm:flex-row sm:justify-between gap-3 pt-4 border-t border-gray-100">

                            <button
                                type="button"
                                onclick="if(confirm('Hapus Hero ini? Gambar Hero juga akan dihapus dari penyimpanan.')) document.getElementById('delete-hero-form').submit();"
                                class="inline-flex justify-center items-center px-4 py-2
                                       rounded-md bg-red-50 text-red-700
                                       font-semibold text-xs uppercase tracking-widest
                                       hover:bg-red-100"
                            >
                                Hapus Hero
                            </button>

                            <div class="flex flex-col-reverse sm:flex-row gap-3">

                                <a
                                    href="{{ route('heroes.index') }}"
                                    class="inline-flex justify-center items-center px-4 py-2
                                           border border-gray-300 rounded-md
                                           font-semibold text-xs text-gray-700 uppercase
                                           tracking-widest bg-white
                                           hover:bg-gray-50"
                                >
                                    Batal
                                </a>

                                <x-primary-button>
                                    Simpan Perubahan
                                </x-primary-button>

                            </div>

                        </div>

                    </form>

                    {{-- DELETE FORM --}}
                    <form
                        id="delete-hero-form"
                        method="POST"
                        action="{{ route('heroes.destroy', $hero) }}"
                        class="hidden"
                    >
                        @csrf
                        @method('DELETE')
                    </form>

                </div>
            </div>

        </div>
    </div>

</x-app-layout>