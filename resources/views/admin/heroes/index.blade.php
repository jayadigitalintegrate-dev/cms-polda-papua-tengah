<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Hero Website
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Kelola gambar, status, dan urutan Hero website Polda Papua Tengah.
                </p>
            </div>

            <a href="{{ route('heroes.create') }}"
               class="inline-flex items-center justify-center px-4 py-2 bg-green-700 text-white rounded-md text-sm font-semibold hover:bg-green-800">
                + Tambah Hero
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                    <ul class="list-disc list-inside text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">
                    Daftar Hero
                </h3>

                <p class="text-sm text-gray-500 mb-5">
                    Hero aktif ditampilkan berdasarkan urutan terkecil.
                </p>

                @if ($heroes->count())
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Preview</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Hero ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Urutan</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($heroes as $hero)
                                    <tr>
                                        <td class="px-4 py-4">
                                            <div class="w-40 h-20 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden">
                                                <img
                                                    src="{{ asset('storage/' . $hero->image) }}"
                                                    alt="Hero #{{ $hero->id }}"
                                                    class="w-full h-full object-contain"
                                                >
                                            </div>
                                        </td>

                                        <td class="px-4 py-4 font-semibold text-gray-800">
                                            Hero #{{ $hero->id }}
                                        </td>

                                        <td class="px-4 py-4">
                                            @if ($hero->status === 'active')
                                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-4 text-center font-semibold text-gray-700">
                                            {{ $hero->sort_order }}
                                        </td>

                                        <td class="px-4 py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('heroes.edit', $hero) }}"
                                                   class="px-3 py-2 rounded-md bg-indigo-50 text-indigo-700 text-xs font-semibold hover:bg-indigo-100">
                                                    Edit
                                                </a>

                                                <form method="POST"
                                                      action="{{ route('heroes.destroy', $hero) }}"
                                                      onsubmit="return confirm('Hapus Hero ini? Gambar Hero juga akan dihapus.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="px-3 py-2 rounded-md bg-red-50 text-red-700 text-xs font-semibold hover:bg-red-100">
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

                    <div class="md:hidden space-y-4">
                        @foreach ($heroes as $hero)
                            <div class="rounded-xl border border-gray-200 overflow-hidden">
                                <div class="aspect-[2/1] bg-gray-100">
                                    <img
                                        src="{{ asset('storage/' . $hero->image) }}"
                                        alt="Hero #{{ $hero->id }}"
                                        class="w-full h-full object-contain"
                                    >
                                </div>

                                <div class="p-4 space-y-3">
                                    <div class="flex justify-between gap-3">
                                        <h4 class="font-semibold text-gray-800">
                                            Hero #{{ $hero->id }}
                                        </h4>

                                        <span class="text-sm text-gray-600">
                                            {{ $hero->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>

                                    <p class="text-sm text-gray-600">
                                        Urutan: {{ $hero->sort_order }}
                                    </p>

                                    <div class="flex gap-2">
                                        <a href="{{ route('heroes.edit', $hero) }}"
                                           class="flex-1 text-center px-3 py-2 rounded-md bg-indigo-50 text-indigo-700 text-sm font-semibold">
                                            Edit
                                        </a>

                                        <form method="POST"
                                              action="{{ route('heroes.destroy', $hero) }}"
                                              class="flex-1"
                                              onsubmit="return confirm('Hapus Hero ini? Gambar Hero juga akan dihapus.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="w-full px-3 py-2 rounded-md bg-red-50 text-red-700 text-sm font-semibold">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $heroes->links() }}
                    </div>
                @else
                    <div class="text-center py-14">
                        <h3 class="text-lg font-semibold text-gray-700">
                            Belum ada Hero
                        </h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Tambahkan gambar Hero pertama untuk website.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>