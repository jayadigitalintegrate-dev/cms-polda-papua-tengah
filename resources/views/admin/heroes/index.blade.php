<x-app-layout>

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Hero Website
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola banner Hero yang ditampilkan pada website Polda Papua Tengah.
                </p>
            </div>

            <a
                href="{{ route('heroes.create') }}"
                class="inline-flex items-center justify-center px-4 py-2
                       bg-green-700 border border-transparent rounded-md
                       font-semibold text-xs text-white uppercase tracking-widest
                       hover:bg-green-800 focus:bg-green-800
                       active:bg-green-900 focus:outline-none
                       focus:ring-2 focus:ring-green-500 focus:ring-offset-2
                       transition ease-in-out duration-150"
            >
                + Tambah Hero
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- SUCCESS MESSAGE --}}
            @if (session('success'))
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="mb-5 rounded-lg border border-green-200 bg-green-50
                           px-4 py-3 text-sm text-green-700"
                >
                    {{ session('success') }}
                </div>
            @endif

            {{-- VALIDATION ERROR --}}
            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                    <p class="font-semibold text-sm text-red-700">
                        Terjadi kesalahan:
                    </p>

                    <ul class="mt-2 list-disc list-inside text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                <div class="p-6">

                    {{-- HEADER TABLE --}}
                    <div class="flex flex-col gap-2 mb-5">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Daftar Hero
                        </h3>

                        <p class="text-sm text-gray-500">
                            Urutan Hero mengikuti nilai <strong>Urutan</strong>.
                            Hero dengan status aktif dapat ditampilkan pada website.
                        </p>
                    </div>

                    @if ($heroes->count())

                        {{-- DESKTOP TABLE --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">

                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold
                                                   text-gray-500 uppercase tracking-wider"
                                        >
                                            Preview
                                        </th>

                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold
                                                   text-gray-500 uppercase tracking-wider"
                                        >
                                            Judul
                                        </th>

                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold
                                                   text-gray-500 uppercase tracking-wider"
                                        >
                                            Status
                                        </th>

                                        <th
                                            class="px-4 py-3 text-center text-xs font-semibold
                                                   text-gray-500 uppercase tracking-wider"
                                        >
                                            Urutan
                                        </th>

                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold
                                                   text-gray-500 uppercase tracking-wider"
                                        >
                                            Publikasi
                                        </th>

                                        <th
                                            class="px-4 py-3 text-right text-xs font-semibold
                                                   text-gray-500 uppercase tracking-wider"
                                        >
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="bg-white divide-y divide-gray-200">

                                    @foreach ($heroes as $hero)

                                        <tr class="hover:bg-gray-50">

                                            {{-- IMAGE --}}
                                            <td class="px-4 py-4">
                                                <div class="w-40 h-20 overflow-hidden rounded-lg bg-gray-100 border border-gray-200">
                                                    <img
                                                        src="{{ asset('storage/' . $hero->image) }}"
                                                        alt="{{ $hero->title }}"
                                                        class="w-full h-full object-cover"
                                                    >
                                                </div>
                                            </td>

                                            {{-- TITLE --}}
                                            <td class="px-4 py-4 align-top">
                                                <div class="font-semibold text-gray-800">
                                                    {{ $hero->title }}
                                                </div>

                                                @if ($hero->subtitle)
                                                    <div class="mt-1 text-sm text-gray-500 line-clamp-2">
                                                        {{ $hero->subtitle }}
                                                    </div>
                                                @endif

                                                @if ($hero->button_text)
                                                    <div class="mt-2 text-xs text-gray-400">
                                                        CTA:
                                                        <span class="font-medium text-gray-600">
                                                            {{ $hero->button_text }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </td>

                                            {{-- STATUS --}}
                                            <td class="px-4 py-4 align-top">

                                                @if ($hero->status === 'active')
                                                    <span
                                                        class="inline-flex items-center rounded-full
                                                               bg-green-100 px-2.5 py-1 text-xs
                                                               font-semibold text-green-700"
                                                    >
                                                        Aktif
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center rounded-full
                                                               bg-gray-100 px-2.5 py-1 text-xs
                                                               font-semibold text-gray-600"
                                                    >
                                                        Nonaktif
                                                    </span>
                                                @endif

                                            </td>

                                            {{-- SORT --}}
                                            <td class="px-4 py-4 text-center align-top">
                                                <span class="font-semibold text-gray-700">
                                                    {{ $hero->sort_order }}
                                                </span>
                                            </td>

                                            {{-- PUBLISHED --}}
                                            <td class="px-4 py-4 align-top">

                                                @if ($hero->published_at)
                                                    <div class="text-sm text-gray-700">
                                                        {{ $hero->published_at->format('d/m/Y') }}
                                                    </div>

                                                    <div class="text-xs text-gray-400">
                                                        {{ $hero->published_at->format('H:i') }}
                                                    </div>
                                                @else
                                                    <span class="text-sm text-gray-400">
                                                        Segera
                                                    </span>
                                                @endif

                                            </td>

                                            {{-- ACTION --}}
                                            <td class="px-4 py-4 text-right align-top">

                                                <div class="flex justify-end gap-2">

                                                    <a
                                                        href="{{ route('heroes.edit', $hero) }}"
                                                        class="inline-flex items-center px-3 py-2
                                                               rounded-md bg-indigo-50 text-indigo-700
                                                               text-xs font-semibold
                                                               hover:bg-indigo-100"
                                                    >
                                                        Edit
                                                    </a>

                                                    <form
                                                        method="POST"
                                                        action="{{ route('heroes.destroy', $hero) }}"
                                                        onsubmit="return confirm('Hapus Hero ini? Gambar Hero juga akan dihapus dari penyimpanan.');"
                                                    >
                                                        @csrf
                                                        @method('DELETE')

                                                        <button
                                                            type="submit"
                                                            class="inline-flex items-center px-3 py-2
                                                                   rounded-md bg-red-50 text-red-700
                                                                   text-xs font-semibold
                                                                   hover:bg-red-100"
                                                        >
                                                            Hapus
                                                        </button>
                                                    </form>

                                                </div>

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>
                        </div>

                        {{-- MOBILE CARD --}}
                        <div class="md:hidden space-y-4">

                            @foreach ($heroes as $hero)

                                <div class="border border-gray-200 rounded-xl overflow-hidden">

                                    <div class="aspect-[16/8] bg-gray-100">
                                        <img
                                            src="{{ asset('storage/' . $hero->image) }}"
                                            alt="{{ $hero->title }}"
                                            class="w-full h-full object-cover"
                                        >
                                    </div>

                                    <div class="p-4">

                                        <div class="flex items-start justify-between gap-3">

                                            <div>
                                                <h4 class="font-semibold text-gray-800">
                                                    {{ $hero->title }}
                                                </h4>

                                                @if ($hero->subtitle)
                                                    <p class="mt-1 text-sm text-gray-500">
                                                        {{ $hero->subtitle }}
                                                    </p>
                                                @endif
                                            </div>

                                            @if ($hero->status === 'active')
                                                <span
                                                    class="shrink-0 rounded-full bg-green-100
                                                           px-2 py-1 text-xs font-semibold text-green-700"
                                                >
                                                    Aktif
                                                </span>
                                            @else
                                                <span
                                                    class="shrink-0 rounded-full bg-gray-100
                                                           px-2 py-1 text-xs font-semibold text-gray-600"
                                                >
                                                    Nonaktif
                                                </span>
                                            @endif

                                        </div>

                                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">

                                            <div>
                                                <div class="text-xs text-gray-400">
                                                    Urutan
                                                </div>

                                                <div class="font-semibold text-gray-700">
                                                    {{ $hero->sort_order }}
                                                </div>
                                            </div>

                                            <div>
                                                <div class="text-xs text-gray-400">
                                                    Publikasi
                                                </div>

                                                <div class="font-semibold text-gray-700">
                                                    {{ $hero->published_at
                                                        ? $hero->published_at->format('d/m/Y H:i')
                                                        : 'Segera'
                                                    }}
                                                </div>
                                            </div>

                                        </div>

                                        <div class="mt-4 flex gap-2">

                                            <a
                                                href="{{ route('heroes.edit', $hero) }}"
                                                class="flex-1 text-center px-3 py-2 rounded-md
                                                       bg-indigo-50 text-indigo-700
                                                       text-xs font-semibold hover:bg-indigo-100"
                                            >
                                                Edit
                                            </a>

                                            <form
                                                method="POST"
                                                action="{{ route('heroes.destroy', $hero) }}"
                                                class="flex-1"
                                                onsubmit="return confirm('Hapus Hero ini? Gambar Hero juga akan dihapus dari penyimpanan.');"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="w-full px-3 py-2 rounded-md
                                                           bg-red-50 text-red-700
                                                           text-xs font-semibold hover:bg-red-100"
                                                >
                                                    Hapus
                                                </button>
                                            </form>

                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                        {{-- PAGINATION --}}
                        <div class="mt-6">
                            {{ $heroes->links() }}
                        </div>

                    @else

                        <div class="text-center py-14">

                            <div class="text-gray-400 text-5xl mb-4">
                                🖼️
                            </div>

                            <h3 class="text-lg font-semibold text-gray-700">
                                Belum ada Hero
                            </h3>

                            <p class="mt-2 text-sm text-gray-500">
                                Tambahkan gambar Hero pertama untuk ditampilkan pada website.
                            </p>

                            <a
                                href="{{ route('heroes.create') }}"
                                class="inline-flex mt-5 items-center px-4 py-2
                                       bg-green-700 text-white rounded-md
                                       text-sm font-semibold hover:bg-green-800"
                            >
                                + Tambah Hero
                            </a>

                        </div>

                    @endif

                </div>

            </div>

        </div>
    </div>

</x-app-layout>