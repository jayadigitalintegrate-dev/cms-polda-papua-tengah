<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail Pengaduan Masyarakat
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Detail laporan dan informasi tindak lanjut pengaduan.
                </p>
            </div>

            <a
                href="{{ route('complaints.index') }}"
                class="inline-flex items-center rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
            >
                ? Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- INFORMASI TIKET --}}
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden">

                <div class="border-b border-gray-200 px-6 py-5">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                        <div>
                            <p class="text-sm text-gray-500">
                                Nomor Tiket Laporan
                            </p>

                            <h1 class="mt-1 text-2xl font-bold text-gray-900">
                                {{ $complaint->tiket }}
                            </h1>
                        </div>

                        <div>
                            @if ($complaint->status === 'diterima')

                                <span class="inline-flex rounded-full bg-blue-100 px-4 py-2 text-sm font-semibold text-blue-800">
                                    Laporan Diterima
                                </span>

                            @elseif ($complaint->status === 'diverifikasi')

                                <span class="inline-flex rounded-full bg-purple-100 px-4 py-2 text-sm font-semibold text-purple-800">
                                    Diverifikasi
                                </span>

                            @elseif ($complaint->status === 'diproses')

                                <span class="inline-flex rounded-full bg-yellow-100 px-4 py-2 text-sm font-semibold text-yellow-800">
                                    Sedang Diproses
                                </span>

                            @elseif ($complaint->status === 'selesai')

                                <span class="inline-flex rounded-full bg-green-100 px-4 py-2 text-sm font-semibold text-green-800">
                                    Selesai
                                </span>

                            @elseif ($complaint->status === 'ditolak')

                                <span class="inline-flex rounded-full bg-red-100 px-4 py-2 text-sm font-semibold text-red-800">
                                    Ditolak
                                </span>

                            @else

                                <span class="inline-flex rounded-full bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-800">
                                    {{ ucfirst($complaint->status) }}
                                </span>

                            @endif
                        </div>

                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 px-6 py-6">

                    <div>
                        <p class="text-sm text-gray-500">
                            Tanggal Masuk
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $complaint->created_at?->format('d/m/Y H:i') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Jenis Pengaduan
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $complaint->jenis }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Terakhir Diproses
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $complaint->processed_at?->format('d/m/Y H:i') ?? 'Belum diproses' }}
                        </p>
                    </div>

                </div>

            </div>


            {{-- IDENTITAS PELAPOR --}}
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden">

                <div class="border-b border-gray-200 px-6 py-5">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Identitas Pelapor
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Data yang disampaikan oleh pelapor melalui website.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-6 py-6">

                    <div>
                        <p class="text-sm text-gray-500">
                            Nama Lengkap
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $complaint->nama }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Nomor KTP / Identitas
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $complaint->ktp }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Nomor HP
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $complaint->hp }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Email
                        </p>

                        <p class="mt-1 font-semibold text-gray-900 break-all">
                            {{ $complaint->email }}
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500">
                            Alamat
                        </p>

                        <p class="mt-1 text-gray-900 whitespace-pre-line">
                            {{ $complaint->alamat }}
                        </p>
                    </div>

                </div>

            </div>


            {{-- ISI LAPORAN --}}
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden">

                <div class="border-b border-gray-200 px-6 py-5">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Isi Pengaduan
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Isi laporan sebagaimana disampaikan oleh pelapor.
                    </p>
                </div>

                <div class="px-6 py-6">

                    <div class="rounded-lg bg-gray-50 border border-gray-200 p-5">

                        <p class="text-gray-800 leading-7 whitespace-pre-line">
                            {{ $complaint->isi }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- CATATAN ADMIN --}}
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden">

                <div class="border-b border-gray-200 px-6 py-5">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Catatan Tindak Lanjut
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Catatan internal petugas/admin terkait penanganan laporan.
                    </p>
                </div>

                <div class="px-6 py-6">

                    @if ($complaint->catatan_admin)

                        <div class="rounded-lg bg-gray-50 border border-gray-200 p-5">
                            <p class="text-gray-800 leading-7 whitespace-pre-line">
                                {{ $complaint->catatan_admin }}
                            </p>
                        </div>

                    @else

                        <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-5">
                            <p class="text-yellow-800">
                                Belum ada catatan tindak lanjut dari petugas.
                            </p>
                        </div>

                    @endif

                </div>

            </div>


            {{-- PETUGAS --}}
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden">

                <div class="border-b border-gray-200 px-6 py-5">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Informasi Pemrosesan
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-6 py-6">

                    <div>
                        <p class="text-sm text-gray-500">
                            Petugas / Admin
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $complaint->processor?->name ?? 'Belum ditentukan' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Waktu Pemrosesan
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $complaint->processed_at?->format('d/m/Y H:i') ?? 'Belum diproses' }}
                        </p>
                    </div>

                </div>

            </div>


            {{-- TOMBOL --}}
            <div class="flex flex-col sm:flex-row justify-between gap-3">

                <a
                    href="{{ route('complaints.index') }}"
                    class="inline-flex justify-center items-center rounded-lg bg-gray-600 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-700"
                >
                    ? Kembali ke Pengaduan
                </a>

            </div>

        </div>

    </div>

</x-app-layout>
