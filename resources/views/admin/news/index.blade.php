<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between">

            <div>
                <h2 class="font-semibold text-2xl text-gray-800">
                    Manajemen Berita
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Kelola seluruh berita yang akan ditampilkan pada Website Polda Papua Tengah.
                </p>
            </div>

            <a href="{{ route('news.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-5 py-3 font-semibold text-white shadow hover:bg-green-700 transition">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-5 h-5"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 4v16m8-8H4"/>

                </svg>

                <span class="text-white">
                    Tambah Berita
                </span>

            </a>

        </div>
    </x-slot>

    <div class="py-10">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))

                <div class="mb-6 rounded-lg border border-green-300 bg-green-100 p-4 text-green-700">

                    {{ session('success') }}

                </div>

            @endif


            <div class="rounded-xl bg-white shadow">

                <div class="flex items-center justify-between border-b px-6 py-5">

                    <div>

                        <h3 class="text-lg font-bold">
                            Daftar Berita
                        </h3>

                        <p class="text-sm text-gray-500">
                            Total Berita :
                            <strong>{{ $news->total() }}</strong>
                        </p>

                    </div>

                    <a href="{{ route('news.create') }}"
                       class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">

                        Tambah Berita

                    </a>

                </div>


                @if($news->count())

                    <div class="overflow-x-auto">

                        <table class="min-w-full">

                            <thead class="bg-gray-100">

                                <tr>

                                    <th class="px-6 py-3 text-left">
                                        Judul
                                    </th>

                                    <th class="px-6 py-3 text-left">
                                        Kategori
                                    </th>

                                    <th class="px-6 py-3 text-left">
                                        Status
                                    </th>

                                    <th class="px-6 py-3 text-center">
                                        Aksi
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                @foreach($news as $item)

                                    <tr class="border-b">

                                        <td class="px-6 py-4">

                                            <div class="font-semibold">

                                                {{ $item->title }}

                                            </div>

                                        </td>

                                        <td class="px-6 py-4">

                                            {{ $item->category }}

                                        </td>

                                        <td class="px-6 py-4">

                                            <span class="rounded bg-gray-100 px-3 py-1 text-sm">

                                                {{ ucfirst($item->status) }}

                                            </span>

                                        </td>

                                        <td class="px-6 py-4 text-center">

                                            <a href="{{ route('news.edit',$item) }}"
                                               class="mr-3 text-blue-600 hover:underline">

                                                Edit

                                            </a>

                                            <form method="POST"
                                                  action="{{ route('news.destroy',$item) }}"
                                                  class="inline">

                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    onclick="return confirm('Yakin ingin menghapus berita ini?')"
                                                    class="text-red-600 hover:underline">

                                                    Hapus

                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                    <div class="p-6">

                        {{ $news->links() }}

                    </div>

                @else

                    <div class="py-16 text-center">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="mx-auto h-16 w-16 text-gray-300"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.5"
                                  d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h8l8 8v8a2 2 0 01-2 2z"/>

                        </svg>

                        <h3 class="mt-4 text-xl font-semibold">

                            Belum Ada Berita

                        </h3>

                        <p class="mt-2 text-gray-500">

                            Silakan tambahkan berita pertama untuk Website Polda Papua Tengah.

                        </p>

                        <a href="{{ route('news.create') }}"
                           class="mt-6 inline-flex items-center rounded-lg bg-green-600 px-6 py-3 font-semibold text-white hover:bg-green-700">

                            ➕ Tambah Berita

                        </a>

                    </div>

                @endif

            </div>

        </div>

    </div>

</x-app-layout>