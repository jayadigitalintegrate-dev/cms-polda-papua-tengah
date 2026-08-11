<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Jajaran Polres Wilayah Hukum Papua Tengah
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola data Kepolisian Resor di wilayah hukum Polda Papua Tengah.
                </p>
            </div>

            <a
                href="{{ route('police-stations.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
            >
                + Tambah Polres
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-200 px-6 py-5">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Daftar Jajaran Polres
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Data Polres yang berada dalam wilayah hukum Polda Papua Tengah.
                    </p>
                </div>

                @if ($policeStations->count())

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        No.
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        Polres
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        Kapolres
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        Status
                                    </th>

                                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($policeStations as $index => $policeStation)
                                    <tr class="transition hover:bg-gray-50">

                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            {{ $policeStations->firstItem() + $index }}
                                        </td>

                                        <td class="px-6 py-4">
                                            <div class="text-sm font-semibold text-gray-900">
                                                {{ $policeStation->name_id }}
                                            </div>

                                            @if ($policeStation->name_en)
                                                <div class="mt-1 text-xs text-gray-500">
                                                    {{ $policeStation->name_en }}
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4">
                                            @if ($policeStation->chief_name)
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $policeStation->chief_name }}
                                                </div>

                                                @if ($policeStation->chief_rank)
                                                    <div class="mt-1 text-xs text-gray-500">
                                                        {{ $policeStation->chief_rank }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-sm text-gray-400">
                                                    Belum diisi
                                                </span>
                                            @endif
                                        </td>

                                        <td class="whitespace-nowrap px-6 py-4">
                                            @if ($policeStation->status === 'active')
                                                <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                                    Tidak Aktif
                                                </span>
                                            @endif
                                        </td>

                                        <td class="whitespace-nowrap px-6 py-4 text-right">
                                            <div class="flex justify-end gap-2">

                                                <a
                                                    href="{{ route('police-stations.show', $policeStation) }}"
                                                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50"
                                                >
                                                    Lihat
                                                </a>

                                                <a
                                                    href="{{ route('police-stations.edit', $policeStation) }}"
                                                    class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700"
                                                >
                                                    Edit
                                                </a>

                                                <form
                                                    method="POST"
                                                    action="{{ route('police-stations.destroy', $policeStation) }}"
                                                    onsubmit="return confirm('Hapus data Polres ini?');"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-red-700"
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

                    <div class="border-t border-gray-200 px-6 py-4">
                        {{ $policeStations->links() }}
                    </div>

                @else

                    <div class="px-6 py-12 text-center">
                        <div class="text-sm font-medium text-gray-900">
                            Belum ada data Polres.
                        </div>

                        <p class="mt-1 text-sm text-gray-500">
                            Silakan tambahkan data Jajaran Polres Wilayah Hukum Papua Tengah.
                        </p>

                        <a
                            href="{{ route('police-stations.create') }}"
                            class="mt-5 inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700"
                        >
                            + Tambah Polres
                        </a>
                    </div>

                @endif

            </div>
        </div>
    </div>
</x-app-layout>
