<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Galeri
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if($errors->any())
                <div class="mb-6 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">

                    <form
                        method="POST"
                        action="{{ route('galleries.update', $gallery) }}"
                        enctype="multipart/form-data"
                    >
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">

                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">
                                    Judul
                                </label>

                                <input
                                    id="title"
                                    name="title"
                                    type="text"
                                    value="{{ old('title', $gallery->title) }}"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >

                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="gallery_category_id" class="block text-sm font-medium text-gray-700">
                                    Kategori Galeri
                                </label>

                                <select
                                    id="gallery_category_id"
                                    name="gallery_category_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">-- Pilih Kategori --</option>

                                    @foreach($categories as $category)
                                        <option
                                            value="{{ $category->id }}"
                                            @selected(old('gallery_category_id', $gallery->gallery_category_id) == $category->id)
                                        >
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @if($categories->isEmpty())
                                    <p class="mt-1 text-xs text-gray-500">
                                        Belum ada kategori aktif.
                                        <a href="{{ route('gallery-categories.index') }}" class="text-indigo-600 hover:text-indigo-900">Kelola Kategori Galeri</a>
                                    </p>
                                @endif

                                @error('gallery_category_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            @if($isCollection)

                            {{-- Koleksi foto (Galeri Dokumentasi) --}}
                            <div
                                x-data="{
                                    existing: @js($gallery->images->count()),
                                    maxImages: @js($maxImages),
                                    removed: 0,
                                    added: 0,
                                    get total() { return this.existing - this.removed + this.added; },
                                }"
                            >
                                <label class="block text-sm font-medium text-gray-700">
                                    {{ $isMediaCenter ? 'Foto Media Center' : 'Foto Koleksi' }}
                                </label>

                                <p class="mt-1 text-xs text-gray-500">
                                    Centang foto yang ingin dihapus. Foto lama tidak dihapus kecuali dicentang.
                                    Foto pertama menjadi sampul koleksi.
                                </p>

                                <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-5">
                                    @foreach($gallery->images as $photo)
                                        <label class="block cursor-pointer rounded-md border border-gray-200 p-1 text-xs text-gray-600">
                                            <img
                                                src="{{ asset('storage/' . $photo->image) }}"
                                                alt="Foto {{ $loop->iteration }}"
                                                class="h-24 w-full rounded object-cover"
                                            >

                                            <span class="mt-1 flex items-center gap-1">
                                                <input
                                                    type="checkbox"
                                                    name="delete_images[]"
                                                    value="{{ $photo->id }}"
                                                    @checked(in_array($photo->id, old('delete_images', [])))
                                                    x-on:change="removed += $event.target.checked ? 1 : -1"
                                                    class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                                >
                                                Hapus foto {{ $loop->iteration }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                <label for="photos" class="mt-4 block text-sm font-medium text-gray-700">
                                    Tambah Foto
                                </label>

                                <input
                                    id="photos"
                                    name="photos[]"
                                    type="file"
                                    multiple
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                    x-on:change="added = $event.target.files.length"
                                    class="mt-1 block w-full rounded-md border border-gray-300 text-sm"
                                >

                                <p class="mt-1 text-xs text-gray-500">
                                    Total koleksi maksimal {{ $maxImages }} foto. Format JPG, PNG, atau WEBP. Maksimal 5 MB per foto.
                                </p>

                                <p
                                    class="mt-1 text-sm"
                                    x-bind:class="total > maxImages || total < 1 ? 'text-red-600' : 'text-gray-600'"
                                    x-text="'Total setelah disimpan: ' + total + ' / ' + maxImages + ' foto'
                                        + (total > maxImages ? ' — melebihi batas.' : (total < 1 ? ' — minimal 1 foto.' : ''))"
                                ></p>

                                @error('photos')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror

                                @foreach($errors->get('photos.*') as $messages)
                                    @foreach($messages as $message)
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @endforeach
                                @endforeach

                                @foreach($errors->get('delete_images.*') as $messages)
                                    @foreach($messages as $message)
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @endforeach
                                @endforeach
                            </div>

                            @else

                            <div>
                                <label for="image" class="block text-sm font-medium text-gray-700">
                                    Foto
                                </label>

                                @if($gallery->image)
                                    <img
                                        src="{{ asset('storage/' . $gallery->image) }}"
                                        alt="{{ $gallery->title }}"
                                        class="mt-2 mb-2 h-40 rounded-md object-cover border border-gray-200"
                                    >
                                @endif

                                <input
                                    id="image"
                                    name="image"
                                    type="file"
                                    accept=".jpg,.jpeg,.png,.webp"
                                    class="mt-1 block w-full rounded-md border border-gray-300 text-sm"
                                >

                                <p class="mt-1 text-xs text-gray-500">
                                    Kosongkan jika tidak mengganti foto. Format JPG, PNG, atau WEBP. Maksimal 5 MB.
                                </p>

                                @error('image')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            @endif

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">
                                    Deskripsi
                                </label>

                                <textarea
                                    id="description"
                                    name="description"
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >{{ old('description', $gallery->description) }}</textarea>

                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            @if($isMediaCenter)
                            {{-- Isi berita (khusus Media Center) --}}
                            <div>
                                <label for="content" class="block text-sm font-medium text-gray-700">
                                    Isi Berita
                                </label>

                                <textarea
                                    id="content"
                                    name="content"
                                    rows="12"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >{{ old('content', $gallery->content) }}</textarea>

                                <p class="mt-1 text-xs text-gray-500">
                                    Teks biasa; pisahkan paragraf dengan baris baru. Tag HTML tidak disimpan.
                                </p>

                                @error('content')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            @endif

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

                                <div>
                                    <label for="taken_at" class="block text-sm font-medium text-gray-700">
                                        Tanggal Kegiatan/Foto
                                    </label>

                                    <input
                                        id="taken_at"
                                        name="taken_at"
                                        type="date"
                                        value="{{ old('taken_at', $gallery->taken_at?->format('Y-m-d')) }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >

                                    @error('taken_at')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">
                                        Status
                                    </label>

                                    <select
                                        id="status"
                                        name="status"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="draft" @selected(old('status', $gallery->status) === 'draft')>Draft</option>
                                        <option value="published" @selected(old('status', $gallery->status) === 'published')>Published</option>
                                    </select>

                                    @error('status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="sort_order" class="block text-sm font-medium text-gray-700">
                                        Urutan
                                    </label>

                                    <input
                                        id="sort_order"
                                        name="sort_order"
                                        type="number"
                                        min="0"
                                        value="{{ old('sort_order', $gallery->sort_order) }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >

                                    @error('sort_order')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>

                            <div class="flex items-center">
                                <input
                                    id="featured"
                                    name="featured"
                                    type="checkbox"
                                    value="1"
                                    @checked($errors->any() ? old('featured') : $gallery->featured)
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                >

                                <label for="featured" class="ml-2 text-sm text-gray-700">
                                    Featured (tampilkan sebagai unggulan)
                                </label>
                            </div>

                        </div>

                        <div class="mt-8 flex items-center justify-end gap-3">

                            <a
                                href="{{ route('galleries.index') }}"
                                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Batal
                            </a>

                            <button
                                type="submit"
                                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
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
