<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">

            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail Permohonan Informasi PPID
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Detail permohonan dan proses tindak lanjut informasi publik.
                </p>
            </div>

            <a
                href="{{ route('ppid-requests.index') }}"
                class="inline-flex items-center rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
            >
                Kembali
            </a>

        </div>
    </x-slot>


    <div class="py-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">


            {{-- SUCCESS --}}
            @if (session('success'))

                <div class="rounded-lg bg-green-100 border border-green-200 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>

            @endif


            {{-- VALIDATION ERROR --}}
            @if ($errors->any())

                <div class="rounded-lg bg-red-100 border border-red-200 px-4 py-3 text-red-800">

                    <p class="font-semibold mb-2">
                        Terdapat kesalahan:
                    </p>

                    <ul class="list-disc list-inside space-y-1 text-sm">

                        @foreach ($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- INFORMASI TIKET --}}
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden">

                <div class="border-b border-gray-200 px-6 py-5">

                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                        <div>

                            <p class="text-sm text-gray-500">
                                Nomor Tiket Permohonan
                            </p>

                            <h1 class="mt-1 text-2xl font-bold text-gray-900">
                                {{ $ppidRequest->ticket }}
                            </h1>

                        </div>


                        <div>

                            @if ($ppidRequest->status === 'diterima')

                                <span class="inline-flex rounded-full bg-blue-100 px-4 py-2 text-sm font-semibold text-blue-800">
                                    Diterima
                                </span>

                            @elseif ($ppidRequest->status === 'diverifikasi')

                                <span class="inline-flex rounded-full bg-purple-100 px-4 py-2 text-sm font-semibold text-purple-800">
                                    Diverifikasi
                                </span>

                            @elseif ($ppidRequest->status === 'diproses')

                                <span class="inline-flex rounded-full bg-yellow-100 px-4 py-2 text-sm font-semibold text-yellow-800">
                                    Sedang Diproses
                                </span>

                            @elseif ($ppidRequest->status === 'selesai')

                                <span class="inline-flex rounded-full bg-green-100 px-4 py-2 text-sm font-semibold text-green-800">
                                    Selesai
                                </span>

                            @elseif ($ppidRequest->status === 'ditolak')

                                <span class="inline-flex rounded-full bg-red-100 px-4 py-2 text-sm font-semibold text-red-800">
                                    Ditolak
                                </span>

                            @else

                                <span class="inline-flex rounded-full bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-800">
                                    {{ ucfirst($ppidRequest->status) }}
                                </span>

                            @endif

                        </div>

                    </div>

                </div>


                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 px-6 py-6">

                    <div>

                        <p class="text-sm text-gray-500">
                            Tanggal Permohonan
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $ppidRequest->created_at?->format('d/m/Y H:i') }}
                        </p>

                    </div>


                    <div>

                        <p class="text-sm text-gray-500">
                            Cara Memperoleh Informasi
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">

                            @if ($ppidRequest->delivery_method === 'softcopy')
                                Softcopy
                            @elseif ($ppidRequest->delivery_method === 'hardcopy')
                                Hardcopy
                            @elseif ($ppidRequest->delivery_method === 'view')
                                Lihat Langsung
                            @else
                                {{ ucfirst($ppidRequest->delivery_method) }}
                            @endif

                        </p>

                    </div>


                    <div>

                        <p class="text-sm text-gray-500">
                            Terakhir Diproses
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $ppidRequest->processed_at?->format('d/m/Y H:i') ?? 'Belum diproses' }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- IDENTITAS PEMOHON --}}
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden">

                <div class="border-b border-gray-200 px-6 py-5">

                    <h2 class="text-lg font-semibold text-gray-900">
                        Identitas Pemohon
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Data pemohon yang disampaikan melalui website.
                    </p>

                </div>


                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-6 py-6">

                    <div>

                        <p class="text-sm text-gray-500">
                            Nama Lengkap
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $ppidRequest->name }}
                        </p>

                    </div>


                    <div>

                        <p class="text-sm text-gray-500">
                            Nomor Identitas
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $ppidRequest->identity_number }}
                        </p>

                    </div>


                    <div>

                        <p class="text-sm text-gray-500">
                            Nomor Telepon
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $ppidRequest->phone }}
                        </p>

                    </div>


                    <div>

                        <p class="text-sm text-gray-500">
                            Email
                        </p>

                        <p class="mt-1 font-semibold text-gray-900 break-all">
                            {{ $ppidRequest->email }}
                        </p>

                    </div>


                    <div class="md:col-span-2">

                        <p class="text-sm text-gray-500">
                            Alamat
                        </p>

                        <p class="mt-1 text-gray-900 whitespace-pre-line">
                            {{ $ppidRequest->address }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- PERMOHONAN INFORMASI --}}
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden">

                <div class="border-b border-gray-200 px-6 py-5">

                    <h2 class="text-lg font-semibold text-gray-900">
                        Permohonan Informasi
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Informasi publik yang diminta oleh pemohon.
                    </p>

                </div>


                <div class="px-6 py-6 space-y-6">

                    <div>

                        <p class="text-sm text-gray-500 mb-2">
                            Informasi yang Diminta
                        </p>

                        <div class="rounded-lg bg-gray-50 border border-gray-200 p-5">

                            <p class="text-gray-800 leading-7 whitespace-pre-line">
                                {{ $ppidRequest->information }}
                            </p>

                        </div>

                    </div>


                    <div>

                        <p class="text-sm text-gray-500 mb-2">
                            Tujuan Penggunaan Informasi
                        </p>

                        <div class="rounded-lg bg-gray-50 border border-gray-200 p-5">

                            <p class="text-gray-800 leading-7 whitespace-pre-line">
                                {{ $ppidRequest->purpose }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            {{-- CATATAN ADMIN --}}
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden">

                <div class="border-b border-gray-200 px-6 py-5">

                    <h2 class="text-lg font-semibold text-gray-900">
                        Catatan Admin
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Catatan internal petugas terkait penanganan permohonan.
                    </p>

                </div>


                <div class="px-6 py-6">

                    @if ($ppidRequest->catatan_admin)

                        <div class="rounded-lg bg-gray-50 border border-gray-200 p-5">

                            <p class="text-gray-800 leading-7 whitespace-pre-line">
                                {{ $ppidRequest->catatan_admin }}
                            </p>

                        </div>

                    @else

                        <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-5">

                            <p class="text-yellow-800">
                                Belum ada catatan dari petugas.
                            </p>

                        </div>

                    @endif

                </div>

            </div>


            {{-- INFORMASI PEMROSESAN --}}
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
                            {{ $ppidRequest->processor?->name ?? 'Belum ditentukan' }}
                        </p>

                    </div>


                    <div>

                        <p class="text-sm text-gray-500">
                            Waktu Pemrosesan
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $ppidRequest->processed_at?->format('d/m/Y H:i') ?? 'Belum diproses' }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- FORM PEMROSESAN --}}
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden">

                <div class="border-b border-gray-200 px-6 py-5">

                    <h2 class="text-lg font-semibold text-gray-900">
                        Proses Permohonan
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Perbarui status dan catatan tindak lanjut permohonan informasi.
                    </p>

                </div>


                <form
                    method="POST"
                    action="{{ route('ppid-requests.update', $ppidRequest) }}"
                    class="px-6 py-6 space-y-6"
                >

                    @csrf
                    @method('PATCH')


                    <div>

                        <label
                            for="status"
                            class="block text-sm font-semibold text-gray-700 mb-2"
                        >
                            Status Permohonan
                        </label>

                        <select
                            id="status"
                            name="status"
                            required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        >

                            <option
                                value="diterima"
                                @selected($ppidRequest->status === 'diterima')
                            >
                                Diterima
                            </option>

                            <option
                                value="diverifikasi"
                                @selected($ppidRequest->status === 'diverifikasi')
                            >
                                Diverifikasi
                            </option>

                            <option
                                value="diproses"
                                @selected($ppidRequest->status === 'diproses')
                            >
                                Diproses
                            </option>

                            <option
                                value="selesai"
                                @selected($ppidRequest->status === 'selesai')
                            >
                                Selesai
                            </option>

                            <option
                                value="ditolak"
                                @selected($ppidRequest->status === 'ditolak')
                            >
                                Ditolak
                            </option>

                        </select>

                    </div>


                    <div>

                        <label
                            for="catatan_admin"
                            class="block text-sm font-semibold text-gray-700 mb-2"
                        >
                            Catatan Admin
                        </label>

                        <textarea
                            id="catatan_admin"
                            name="catatan_admin"
                            rows="6"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Masukkan catatan tindak lanjut..."
                        >{{ old('catatan_admin', $ppidRequest->catatan_admin) }}</textarea>

                    </div>


                    <div class="flex flex-col sm:flex-row justify-between gap-3">

                        <a
                            href="{{ route('ppid-requests.index') }}"
                            class="inline-flex justify-center items-center rounded-lg bg-gray-600 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-700"
                        >
                            Kembali ke Permohonan
                        </a>


                        <button
                            type="submit"
                            class="inline-flex justify-center items-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700"
                        >
                            Simpan Perubahan
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>
