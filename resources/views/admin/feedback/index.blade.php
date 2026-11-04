@php
    $categoryLabels = [
        'apresiasi' => ['Apresiasi', 'bg-green-100 text-green-800'],
        'saran' => ['Saran', 'bg-blue-100 text-blue-800'],
        'kritik' => ['Kritik', 'bg-red-100 text-red-800'],
    ];

    $statusLabels = [
        'baru' => ['Baru', 'bg-blue-100 text-blue-800'],
        'ditinjau' => ['Ditinjau', 'bg-yellow-100 text-yellow-800'],
        'ditindaklanjuti' => ['Ditindaklanjuti', 'bg-green-100 text-green-800'],
        'diarsipkan' => ['Diarsipkan', 'bg-gray-100 text-gray-700'],
    ];
@endphp

<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Kritik &amp; Saran
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Masukan masyarakat yang masuk melalui website Polda Papua Tengah. Tidak ditampilkan kepada publik.
                </p>
            </div>

            <div class="text-sm text-gray-500">
                Total: {{ $feedbacks->total() }} masukan
            </div>

        </div>

    </x-slot>


    <div class="py-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('feedback_delete_error'))
                <div role="alert" class="mb-4 rounded-lg bg-red-100 p-4 text-red-800">{{ session('feedback_delete_error') }}</div>
            @elseif (session('success'))
                <div role="status" class="mb-4 rounded-lg bg-green-100 p-4 text-green-800">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div role="alert" class="mb-4 rounded-lg bg-red-100 p-4 text-red-800">
                    Permintaan tidak valid. Muat ulang daftar dan pilih kembali masukan.
                </div>
            @endif

            {{-- SEARCH & FILTER --}}
            <div class="bg-white shadow-sm sm:rounded-xl mb-6">

                <div class="p-5">

                    <form method="GET" action="{{ route('feedback.index') }}">

                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

                            {{-- SEARCH --}}
                            <div class="md:col-span-2">

                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                    Cari Masukan
                                </label>

                                <input id="search" type="search" name="search" value="{{ $search }}"
                                    placeholder="Isi pesan, nama, email, atau telepon..."
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">

                            </div>


                            {{-- KATEGORI --}}
                            <div>

                                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                                    Kategori
                                </label>

                                <select id="category" name="category"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">

                                    <option value="">Semua Kategori</option>

                                    @foreach ($categoryLabels as $value => [$label])
                                        <option value="{{ $value }}" @selected($category === $value)>{{ $label }}</option>
                                    @endforeach

                                </select>

                            </div>


                            {{-- STATUS --}}
                            <div>

                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                    Status
                                </label>

                                <select id="status" name="status"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">

                                    <option value="">Semua Status</option>

                                    @foreach ($statusLabels as $value => [$label])
                                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                                    @endforeach

                                </select>

                            </div>


                            {{-- BUTTON --}}
                            <div class="flex items-end gap-2">

                                <button type="submit"
                                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                                    Cari
                                </button>

                                <a href="{{ route('feedback.index') }}"
                                    class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-200">
                                    Reset
                                </a>

                            </div>

                        </div>

                    </form>

                </div>

            </div>


            {{-- TABLE --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl"
                x-data="{ selected: [], pageIds: @js(array_map('strval', $feedbacks->modelKeys())) }">

                @can('delete-feedback')
                    <form id="feedback-bulk-delete" method="POST" action="{{ route('feedback.bulk-delete') }}"
                        x-on:submit="if (selected.length === 0 || ! window.confirm('Hapus permanen ' + selected.length + ' masukan terpilih? Tindakan ini tidak dapat dibatalkan.')) $event.preventDefault()"
                        class="border-b border-gray-200 p-4">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="page_scope" value="{{ $pageScope }}">
                        <input type="hidden" name="page" value="{{ $feedbacks->currentPage() }}">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <input type="hidden" name="category" value="{{ $category }}">
                        <input type="hidden" name="status" value="{{ $status }}">
                        <button type="submit" disabled x-bind:disabled="selected.length === 0"
                            class="rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white disabled:opacity-50">
                            Hapus Terpilih (<span x-text="selected.length">0</span>)
                        </button>
                        <p class="mt-2 text-sm text-gray-600">Pilihan hanya berlaku untuk halaman aktif. Penghapusan bersifat permanen.</p>
                    </form>
                @endcan

                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-50">
                            <tr>
                                @can('delete-feedback')
                                    <th class="px-4 py-3">
                                        <input type="checkbox" aria-label="Pilih semua masukan di halaman ini"
                                            x-bind:disabled="pageIds.length === 0"
                                            x-bind:checked="pageIds.length > 0 && selected.length === pageIds.length"
                                            x-effect="$el.indeterminate = selected.length > 0 && selected.length < pageIds.length"
                                            x-on:change="selected = $event.target.checked ? [...pageIds] : []"
                                            class="rounded border-gray-300">
                                    </th>
                                @endcan
                                <th class="w-40 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Tanggal
                                </th>
                                <th class="w-36 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Kategori
                                </th>
                                <th class="w-28 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Rating
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Ringkasan Pesan
                                </th>
                                <th class="w-40 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Status
                                </th>
                                <th class="w-40 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">

                            @forelse ($feedbacks as $feedback)

                                @php
                                    [$categoryLabel, $categoryClass] = $categoryLabels[$feedback->category] ?? [ucfirst($feedback->category), 'bg-gray-100 text-gray-700'];
                                    [$statusLabel, $statusClass] = $statusLabels[$feedback->status] ?? [ucfirst($feedback->status), 'bg-gray-100 text-gray-700'];
                                @endphp

                                <tr class="hover:bg-gray-50">

                                    @can('delete-feedback')
                                        <td class="px-4 py-4">
                                            <input type="checkbox" name="ids[]" value="{{ $feedback->id }}"
                                                form="feedback-bulk-delete" x-model="selected"
                                                aria-label="Pilih masukan {{ $feedback->id }}"
                                                class="rounded border-gray-300">
                                        </td>
                                    @endcan

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $feedback->created_at?->format('d/m/Y H:i') }}
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $categoryClass }}">
                                            {{ $categoryLabel }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-center text-sm font-semibold text-gray-900" title="Rating {{ $feedback->rating }} dari 5">
                                        {{ $feedback->rating }}/5
                                    </td>

                                    <td class="px-6 py-4 max-w-md">
                                        <div class="text-sm text-gray-700 line-clamp-2">
                                            {{ \Illuminate\Support\Str::limit($feedback->message, 140) }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <a href="{{ route('feedback.show', $feedback) }}"
                                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                                            Lihat Detail
                                        </a>
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="{{ auth()->user()->can('delete-feedback') ? 7 : 6 }}" class="px-6 py-12 text-center text-gray-500">
                                        @if ($search || $category || $status)
                                            Tidak ditemukan masukan yang sesuai dengan pencarian/filter.
                                        @else
                                            Belum ada masukan Kritik &amp; Saran yang masuk.
                                        @endif
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- PAGINATION --}}
                @if ($feedbacks->hasPages())
                    <div class="border-t border-gray-200 px-6 py-4">
                        {{ $feedbacks->links() }}
                    </div>
                @endif

            </div>

        </div>

    </div>

</x-app-layout>
