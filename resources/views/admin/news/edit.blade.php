<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Berita
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

                <form method="POST" action="{{ route('news.update', $news) }}" enctype="multipart/form-data">

                    @csrf
                    @method('PUT')



                    {{-- Judul --}}
                    <div class="mb-5">

                        <label class="block font-semibold mb-2">
                            Judul Berita
                        </label>

                        <input type="text" name="title" value="{{ old('title', $news->title) }}"
                            class="w-full rounded-lg border-gray-300">

                        @error('title')
                            <div class="text-red-600 text-sm mt-1">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Category Context --}}
                    <div class="mb-5">
                        <label class="block font-semibold mb-2">
                            Kategori
                        </label>

                        <input
                            type="text"
                            value="{{ $news->newsCategory?->name ?? $news->category }}"
                            class="w-full rounded-lg border-gray-300 bg-gray-100"
                            readonly
                        >

                        <input
                            type="hidden"
                            name="category"
                            value="{{ $news->category }}"
                        >

                        <p class="text-sm text-gray-500 mt-2">
                            Kategori tidak dapat diubah dari editor.
                        </p>

                        @error('category')
                            <div class="text-red-600 text-sm mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    {{-- YouTube Video --}}
                    <div id="youtubeContainer" class="mb-5 {{ $news->category === 'video' ? '' : 'hidden' }}">

                        <label class="block font-semibold mb-2">
                            URL YouTube
                        </label>

                        <input
                            id="youtube_url"
                            type="url"
                            name="youtube_url"
                            value="{{ old('youtube_url', $news->youtube_url) }}"
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

                        <input id="image" type="file" name="image" accept=".webp,.png,.jpg,.jpeg" class="w-full">

                        <p class="text-sm text-gray-500 mt-2">
                            Format: WEBP / PNG / JPG / JPEG
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
                    <div id="documentContainer" class="mb-5">

                        <label class="block font-semibold mb-2">
                            Dokumen PDF
                        </label>

                        @if ($news->document)

                            <div class="mb-3 p-4 rounded-lg border bg-gray-50">

                                <div class="font-semibold text-gray-800">
                                    Dokumen saat ini
                                </div>

                                <div class="text-sm text-gray-600 mt-1">
                                    {{ $news->document_name ?? 'Dokumen PDF' }}
                                </div>

                                <a href="{{ asset('storage/' . $news->document) }}" target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-block mt-3 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold">
                                    Lihat / Download PDF
                                </a>

                            </div>

                        @endif

                        <input id="document" type="file" name="document" accept=".pdf,application/pdf"
                            class="w-full rounded-lg border-gray-300">

                        <p class="text-sm text-gray-500 mt-2">
                            Upload PDF baru jika ingin mengganti dokumen saat ini.
                            Maksimal 10 MB.
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
                        @if($news->images->count())

                            <div class="mb-6">

                                <label class="block font-semibold mb-3">
                                    Galeri Saat Ini
                                </label>

                                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">

                                    @foreach($news->images as $photo)

                                        <img src="{{ asset('storage/' . $photo->image) }}"
                                            class="rounded-lg border shadow h-32 w-full object-cover">

                                    @endforeach

                                </div>

                            </div>

                        @endif
                        <div id="galleryPreview" class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-4">
                        </div>

                    </div>
                    @if ($news->image)

                        <div class="mb-6">

                            <label class="block font-semibold mb-2">
                                Cover Saat Ini
                            </label>

                            <img src="{{ asset('storage/' . $news->image) }}" class="rounded-lg border shadow max-h-80">

                        </div>

                    @endif

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
                            class="w-full rounded-lg border-gray-300">{{ old('excerpt', $news->excerpt) }}</textarea>

                    </div>


                    {{-- Isi Berita --}}
                    <div class="mb-5">

                        <label class="block font-semibold mb-2">
                            Isi Berita
                        </label>

                        @if($news->category === 'video')
                            <p class="text-xs text-gray-500 mb-2">
                                Opsional untuk Berita Video. Konten utama berasal dari URL YouTube.
                            </p>
                        @endif

                        <textarea id="content" name="content" rows="12"
                            class="w-full rounded-lg border-gray-300">{{ old('content', $news->content) }}</textarea>

                        @error('content')
                            <div class="text-red-600 text-sm mt-1">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <hr class="my-8">


                    {{-- Tombol --}}
                    <div class="flex justify-end gap-3">

                        <a href="{{ route('news.index') }}"
                            class="px-5 py-2 rounded-lg bg-gray-500 hover:bg-gray-600 text-white transition">

                            Batal

                        </a>

                        <button type="submit" name="action" value="draft"
                            class="px-5 py-2 rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white transition">

                            Update Draft

                        </button>

                        <button type="submit" name="action" value="publish"
                            class="px-5 py-2 rounded-lg bg-green-700 hover:bg-green-800 text-white transition">

                            Update & Publish

                        </button>

                    </div>
                </form>

            </div>

        </div>

    </div>


    <script>

        document.getElementById('image').addEventListener('change', function (e) {

            const file = e.target.files[0];

            if (!file) return;

            const reader = new FileReader();

            reader.onload = function (ev) {

                document.getElementById('previewImage').src = ev.target.result;

                document.getElementById('previewContainer').classList.remove('hidden');

            };

            reader.readAsDataURL(file);

        });


        document.getElementById('gallery').addEventListener('change', function (e) {

            const preview = document.getElementById('galleryPreview');

            preview.innerHTML = '';

            const files = Array.from(e.target.files);

            if (files.length > 5) {

                alert('Maksimal 5 foto yang dapat diunggah.');

                e.target.value = '';

                return;

            }

            files.forEach(function (file) {

                const reader = new FileReader();

                reader.onload = function (event) {

                    const img = document.createElement('img');

                    img.src = event.target.result;

                    img.className = 'w-full h-32 object-cover rounded-lg border shadow';

                    preview.appendChild(img);

                };

                reader.readAsDataURL(file);

            });

        });

    </script>

</x-app-layout>