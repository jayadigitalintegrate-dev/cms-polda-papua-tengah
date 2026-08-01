<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detail Berita
        </h2>
    </x-slot>

    <div class="py-8">

        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white rounded-lg shadow p-8">

                <h1 class="text-3xl font-bold mb-4">
                    {{ $news->title }}
                </h1>

                <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-6">

                    <span>
                        <strong>Kategori :</strong>
                        {{ $news->newsCategory?->name }}
                    </span>

                    <span>
                        <strong>Status :</strong>
                        {{ ucfirst($news->status) }}
                    </span>

                    <span>
                        <strong>Penulis :</strong>
                        {{ $news->author?->name }}
                    </span>

                    <span>
                        <strong>Tanggal :</strong>
                        {{ optional($news->published_at)->format('d M Y H:i') ?? '-' }}
                    </span>

                </div>

                @if($news->image)

                    <img src="{{ asset('storage/' . $news->image) }}" alt="{{ $news->title }}"
                        class="w-full rounded-lg shadow mb-8">

                @endif

                @if($news->excerpt)

                    <div class="bg-gray-100 rounded-lg p-4 italic mb-8">
                        {{ $news->excerpt }}
                    </div>

                @endif

                <div class="prose max-w-none">

                    {!! nl2br(e($news->content)) !!}

                </div>

                @if($news->images->count())

                    <hr class="my-8">

                    <h3 class="text-2xl font-bold mb-6">

                        Galeri Kegiatan

                    </h3>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">

                        @foreach($news->images as $photo)

                            <div>

                                <img src="{{ asset('storage/' . $photo->image) }}"
                                    class="rounded-lg shadow border hover:scale-105 transition duration-300 cursor-pointer">

                            </div>

                        @endforeach

                    </div>

                @endif

                <hr class="my-8">

                <div class="flex justify-between">

                    <a href="{{ route('news.index') }}"
                        class="px-5 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg">

                        ← Kembali

                    </a>

                    <a href="{{ route('news.edit', $news) }}"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">

                        Edit Berita

                    </a>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>