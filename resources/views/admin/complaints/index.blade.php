<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Pengaduan Masyarakat
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Daftar laporan pengaduan yang masuk melalui website Polda Papua Tengah.
                </p>
            </div>

            <div class="text-sm text-gray-500">
                Total: {{ $complaints->total() }} laporan
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-5 rounded-lg bg-green-100 border border-green-200 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                                    Tiket
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                                    Pelapor
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                                    Jenis
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                                    Status
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                                    Tanggal
                                </th>

                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">

                            @forelse ($complaints as $complaint)

                                <tr class="hover:bg-gray-50">

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-semibold text-gray-900">
                                            {{ $complaint->tiket }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ $complaint->nama }}
                                        </div>

                                        <div class="text-sm text-gray-500">
                                            {{ $complaint->email }}
                                        </div>

                                        <div class="text-sm text-gray-500">
                                            {{ $complaint->hp }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $complaint->jenis }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">

                                        @if ($complaint->status === 'diterima')

                                            <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                                                Laporan Diterima
                                            </span>

                                        @elseif ($complaint->status === 'diproses')

                                            <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">
                                                Diproses
                                            </span>

                                        @elseif ($complaint->status === 'selesai')

                                            <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                                Selesai
                                            </span>

                                        @else

                                            <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                                {{ ucfirst($complaint->status) }}
                                            </span>

                                        @endif

                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $complaint->created_at?->format('d/m/Y H:i') }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right">

                                        <a
                                            href="{{ route('complaints.show', $complaint) }}"
                                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                                        >
                                            Lihat Detail
                                        </a>

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td
                                        colspan="6"
                                        class="px-6 py-12 text-center text-gray-500"
                                    >
                                        Belum ada pengaduan yang masuk.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>
                </div>

                @if ($complaints->hasPages())
                    <div class="border-t border-gray-200 px-6 py-4">
                        {{ $complaints->links() }}
                    </div>
                @endif

            </div>

        </div>
    </div>
</x-app-layout>