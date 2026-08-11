<x-app-layout>

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Pejabat Polda Papua Tengah
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola data pejabat, pimpinan, dan struktur pejabat Polda Papua Tengah.
                </p>
            </div>

            <a
                href="{{ route('officials.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                + Tambah Pejabat
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Success message --}}
            @if (session('success'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Error message --}}
            @if (session('error'))
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <div class="font-semibold">
                        Terjadi kesalahan:
                    </div>

                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Statistics --}}
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-medium text-gray-500">
                        Total Pejabat
                    </div>

                    <div class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $officials->total() }}
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-medium text-gray-500">
                        Halaman Saat Ini
                    </div>

                    <div class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $officials->count() }}
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-medium text-gray-500">
                        Status Aktif
                    </div>

                    <div class="mt-2 text-3xl font-bold text-green-600">
                        {{ $officials->where('status', 'active')->count() }}
                    </div>
                </div>

            </div>

            {{-- Officials table --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Daftar Pejabat
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Data pejabat yang tersimpan di CMS Polda Papua Tengah.
                    </p>
                </div>

                @if ($officials->count() > 0)

                    {{-- Desktop table --}}
                    <div class="hidden overflow-x-auto md:block">
                        <table class="min-w-full divide-y divide-gray-200">

                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500"
                                    >
                                        No
                                    </th>

                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500"
                                    >
                                        Pejabat
                                    </th>

                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500"
                                    >
                                        Pangkat
                                    </th>

                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500"
                                    >
                                        Jabatan
                                    </th>

                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500"
                                    >
                                        NRP
                                    </th>

                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500"
                                    >
                                        Status
                                    </th>

                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500"
                                    >
                                        Aksi
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 bg-white">

                                @foreach ($officials as $index => $official)

                                    <tr class="transition hover:bg-gray-50">

                                        {{-- Number --}}
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            {{ $officials->firstItem() + $index }}
                                        </td>

                                        {{-- Official --}}
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">

                                                <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-full bg-gray-100">
                                                    @if ($official->photo)
                                                        <img
                                                            src="{{ asset('storage/' . $official->photo) }}"
                                                            alt="{{ $official->name_id }}"
                                                            class="h-full w-full object-cover"
                                                        >
                                                    @else
                                                        <div class="flex h-full w-full items-center justify-center text-xs font-semibold text-gray-400">
                                                            N/A
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="ml-4">
                                                    <div class="font-semibold text-gray-900">
                                                        {{ $official->name_id }}
                                                    </div>

                                                    @if ($official->name_en)
                                                        <div class="text-xs text-gray-500">
                                                            {{ $official->name_en }}
                                                        </div>
                                                    @endif
                                                </div>

                                            </div>
                                        </td>

                                        {{-- Rank --}}
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                            {{ $official->rank ?: '-' }}
                                        </td>

                                        {{-- Position --}}
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            <div class="max-w-xs">
                                                {{ $official->position_id }}

                                                @if ($official->position_en)
                                                    <div class="mt-1 text-xs text-gray-400">
                                                        {{ $official->position_en }}
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- NRP --}}
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                            {{ $official->nrp ?: '-' }}
                                        </td>

                                        {{-- Status --}}
                                        <td class="whitespace-nowrap px-6 py-4 text-center">

                                            @if ($official->status === 'active')

                                                <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                                    Aktif
                                                </span>

                                            @else

                                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                                    Tidak Aktif
                                                </span>

                                            @endif

                                        </td>

                                        {{-- Actions --}}
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">

                                            <div class="flex items-center justify-end gap-2">

                                                <a
                                                    href="{{ route('officials.show', $official) }}"
                                                    class="rounded-md bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-200"
                                                >
                                                    Lihat
                                                </a>

                                                <a
                                                    href="{{ route('officials.edit', $official) }}"
                                                    class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100"
                                                >
                                                    Edit
                                                </a>

                                                <form
                                                    action="{{ route('officials.destroy', $official) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pejabat ini?');"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="rounded-md bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100"
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

                    {{-- Mobile cards --}}
                    <div class="divide-y divide-gray-200 md:hidden">

                        @foreach ($officials as $index => $official)

                            <div class="p-5">

                                <div class="flex items-start gap-4">

                                    <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-full bg-gray-100">

                                        @if ($official->photo)

                                            <img
                                                src="{{ asset('storage/' . $official->photo) }}"
                                                alt="{{ $official->name_id }}"
                                                class="h-full w-full object-cover"
                                            >

                                        @else

                                            <div class="flex h-full w-full items-center justify-center text-xs font-semibold text-gray-400">
                                                N/A
                                            </div>

                                        @endif

                                    </div>

                                    <div class="min-w-0 flex-1">

                                        <div class="text-xs text-gray-400">
                                            No. {{ $officials->firstItem() + $index }}
                                        </div>

                                        <h4 class="mt-1 font-semibold text-gray-900">
                                            {{ $official->name_id }}
                                        </h4>

                                        @if ($official->name_en)
                                            <p class="text-xs text-gray-500">
                                                {{ $official->name_en }}
                                            </p>
                                        @endif

                                        <p class="mt-2 text-sm font-medium text-gray-700">
                                            {{ $official->rank ?: '-' }}
                                        </p>

                                        <p class="mt-1 text-sm text-gray-600">
                                            {{ $official->position_id }}
                                        </p>

                                        @if ($official->nrp)
                                            <p class="mt-1 text-xs text-gray-500">
                                                NRP: {{ $official->nrp }}
                                            </p>
                                        @endif

                                        <div class="mt-3">

                                            @if ($official->status === 'active')

                                                <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                                    Aktif
                                                </span>

                                            @else

                                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                                    Tidak Aktif
                                                </span>

                                            @endif

                                        </div>

                                    </div>

                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">

                                    <a
                                        href="{{ route('officials.show', $official) }}"
                                        class="rounded-md bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-200"
                                    >
                                        Lihat
                                    </a>

                                    <a
                                        href="{{ route('officials.edit', $official) }}"
                                        class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-100"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('officials.destroy', $official) }}"
                                        method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pejabat ini?');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="rounded-md bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100"
                                        >
                                            Hapus
                                        </button>
                                    </form>

                                </div>

                            </div>

                        @endforeach

                    </div>

                    {{-- Pagination --}}
                    @if ($officials->hasPages())

                        <div class="border-t border-gray-200 px-6 py-4">
                            {{ $officials->links() }}
                        </div>

                    @endif

                @else

                    {{-- Empty state --}}
                    <div class="px-6 py-16 text-center">

                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-8 w-8 text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 3.375-.625M15 19.128v-.003c0-1.113-.36-2.16-.97-3.004M15 19.128a9.37 9.37 0 0 1-6 0m6 0a9.37 9.37 0 0 0-1.97-3.004M9 19.128v-.003c0-1.113.36-2.16.97-3.004M9 19.128a9.337 9.337 0 0 1-3.375-.625A9.38 9.38 0 0 1 3 19.128m6 0a9.37 9.37 0 0 0 1.97-3.004M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6 7.128a9.38 9.38 0 0 0-2.625.372M6 19.128a9.38 9.38 0 0 1-2.625-.372"
                                />
                            </svg>
                        </div>

                        <h3 class="mt-4 text-base font-semibold text-gray-900">
                            Belum ada data pejabat
                        </h3>

                        <p class="mx-auto mt-2 max-w-md text-sm text-gray-500">
                            Belum terdapat data pejabat Polda Papua Tengah.
                            Silakan tambahkan data pejabat melalui tombol di bawah.
                        </p>

                        <div class="mt-6">

                            <a
                                href="{{ route('officials.create') }}"
                                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
                            >
                                + Tambah Pejabat
                            </a>

                        </div>

                    </div>

                @endif

            </div>

        </div>
    </div>

</x-app-layout>