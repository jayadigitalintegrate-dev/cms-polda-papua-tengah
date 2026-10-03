<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Berita
        </h2>
    </x-slot>

    @if ($errors->any())
        <div class="max-w-5xl mx-auto mt-6">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <strong>Validation Error:</strong>

                <ul class="list-disc ml-6 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="max-w-5xl mx-auto mt-6">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        </div>
    @endif

    <div class="py-12">

        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow rounded-lg p-6">

                <form method="POST" action="{{ route('news.store') }}" enctype="multipart/form-data">

                    @csrf

                    {{-- Judul --}}
                    <div class="mb-5">

                        <label class="block font-semibold mb-2">
                            Judul Berita
                        </label>

                        <input type="text" name="title" value="{{ old('title') }}"
                            class="w-full rounded-lg border-gray-300">

                        @error('title')
                            <div class="text-red-600 text-sm mt-1">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Kategori --}}
                    <div class="mb-5">

                        <label class="block font-semibold mb-2">
                            Kategori
                        </label>

                        <select name="category" class="w-full rounded-lg border-gray-300">

                            <option value="">
                                -- Pilih Kategori --
                            </option>

                            @foreach($categories as $category)

                                <option value="{{ $category->slug }}" @selected(old('category') == $category->slug)>

                                    {{ $category->name }}

                                </option>

                            @endforeach

                        </select>

                        @error('category')
                            <div class="text-red-600 text-sm mt-1">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- YouTube Video --}}
                    <div id="youtubeContainer" class="mb-5 hidden">

                        <label class="block font-semibold mb-2">
                            URL YouTube
                        </label>

                        <input
                            id="youtube_url"
                            type="url"
                            name="youtube_url"
                            value="{{ old('youtube_url') }}"
                            placeholder="https://www.youtube.com/watch?v=... atau https://youtu.be/..."
                            class="w-full rounded-lg border-gray-300"
                        >

                        <p class="text-sm text-gray-500 mt-2">
                            Khusus Berita Video. Masukkan URL video YouTube saja.
                        </p>

                        @error('youtube_url')
                            <div class="text-red-600 text-sm mt-1">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- Cover Berita --}}
                    <div class="mb-5">

                        <label class="block font-semibold mb-2">
                            Cover Berita
                        </label>


                    </div>

                    {{-- Cover Berita --}}
                        <div class="mb-5">

                            <label class="block font-semibold mb-2">
                                Gambar Utama / Cover Berita
                            </label>

                            <input id="image" type="file" name="image" accept=".webp,.png,.jpg,.jpeg"
                                class="w-full rounded-lg border-gray-300">

                            <p class="text-sm text-gray-500 mt-2">
                                Format: WEBP / PNG / JPG / JPEG. Maksimal 5MB.
                            </p>

                            @error('image')
                                <div class="text-red-600 text-sm mt-1">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <p class="text-sm text-gray-500 mt-2">
                            Format: WEBP / PNG
                            <br>
                            Maksimal 5 MB
                        </p>

                        @error('image')
                            <div class="text-red-600 text-sm mt-1">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                                        {{-- Dokumen PDF --}}
                    <div id="documentContainer" class="mb-5 hidden">

                        <label class="block font-semibold mb-2">
                            Dokumen PDF
                        </label>

                        <input
                            id="document"
                            type="file"
                            name="document"
                            accept=".pdf,application/pdf"
                            class="w-full rounded-lg border-gray-300"
                        >

                        <p class="text-sm text-gray-500 mt-2">
                            Upload dokumen resmi dalam format PDF. Maksimal 10 MB.
                        </p>

                        @error('document')
                            <div class="text-red-600 text-sm mt-1">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- Galeri Kegiatan --}}
                    <div class="mb-5">

                        <label class="block font-semibold mb-2">
                            Galeri Kegiatan (Maksimal 5 Foto)
                        </label>

                        <input id="gallery" type="file" name="gallery[]" accept=".webp,.png,.jpg,.jpeg" multiple
                            class="w-full">

                        <p class="text-sm text-gray-500 mt-2">
                            Pilih maksimal 5 foto kegiatan.
                        </p>

                        <div id="galleryPreview" class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-4">
                        </div>

                    </div>


                    {{-- Preview Cover --}}
                    <div id="previewContainer" class="hidden mb-6">

                        <img id="previewImage" class="rounded-lg border shadow max-h-80">

                    </div>


                    {{-- Ringkasan --}}
                    <div class="mb-5">

                        <label class="block font-semibold mb-2">
                            Ringkasan
                        </label>

                        <textarea name="excerpt" rows="3"
                            class="w-full rounded-lg border-gray-300">{{ old('excerpt') }}</textarea>

                    </div>


                    {{-- Isi Berita --}}
                    <div class="mb-5">

                        <label class="block font-semibold mb-2">
                            Isi Berita
                        </label>

                        <textarea id="content" name="content" rows="12"
                            class="w-full rounded-lg border-gray-300">{{ old('content') }}</textarea>

                        @error('content')
                            <div class="text-red-600 text-sm mt-1">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <hr class="my-8">


                    {{-- Tombol --}}
                    <div
                        class="flex justify-end gap-3 mt-8 mb-4 relative z-50 bg-gray-100 p-5 rounded-lg border-2 border-gray-300">

                        <a href="{{ route('news.index') }}"
                            class="px-5 py-2 rounded-lg bg-gray-500 hover:bg-gray-600 text-white transition">

                            Batal

                        </a>


                        <button type="submit" name="action" value="draft"
                            class="px-5 py-2 rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white font-semibold shadow-lg">

                            Simpan Draft

                        </button>


                        <button type="submit" name="action" value="publish"
                            class="px-5 py-2 rounded-lg bg-green-700 hover:bg-green-800 text-white font-semibold shadow-lg">

                            Publish Berita

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.querySelector('select[name="category"]');
    const youtubeContainer = document.getElementById('youtubeContainer');

    function toggleYoutubeField() {
        if (!categorySelect || !youtubeContainer) return;

        youtubeContainer.classList.toggle(
            'hidden',
            categorySelect.value !== 'video'
        );
    }

    categorySelect?.addEventListener('change', toggleYoutubeField);

    toggleYoutubeField();
});
</script>
</x-app-layout>
