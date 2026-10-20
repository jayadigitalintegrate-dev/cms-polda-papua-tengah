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

    [$categoryLabel, $categoryClass] = $categoryLabels[$feedback->category] ?? [ucfirst($feedback->category), 'bg-gray-100 text-gray-700'];
    [$statusLabel, $statusClass] = $statusLabels[$feedback->status] ?? [ucfirst($feedback->status), 'bg-gray-100 text-gray-700'];
@endphp

<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">

            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail Kritik &amp; Saran
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Isi masukan dan proses tindak lanjut. Identitas pengirim bersifat rahasia.
                </p>
            </div>

            <a href="{{ route('feedback.index') }}"
                class="inline-flex items-center rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
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

            {{-- ERRORS --}}
            @if ($errors->any())
                <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-800" role="alert">
                    <ul class="list-disc ps-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            {{-- RINGKASAN --}}
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden">

                <div class="border-b border-gray-200 px-6 py-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full px-4 py-2 text-sm font-semibold {{ $categoryClass }}">
                            {{ $categoryLabel }}
                        </span>
                        <span class="inline-flex rounded-full bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-800">
                            Rating {{ $feedback->rating }}/5
                        </span>
                    </div>

                    <span class="inline-flex rounded-full px-4 py-2 text-sm font-semibold {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>

                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 px-6 py-6">

                    <div>
                        <p class="text-sm text-gray-500">Tanggal Masuk</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ $feedback->created_at?->format('d/m/Y H:i') }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Persetujuan Data</p>
                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $feedback->consent_at?->format('d/m/Y H:i') }} ({{ $feedback->consent_version }})
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Terakhir Ditinjau</p>
                        <p class="mt-1 font-semibold text-gray-900">
                            @if ($feedback->reviewed_at)
                                {{ $feedback->reviewed_at->format('d/m/Y H:i') }}
                                oleh {{ $feedback->reviewer?->name ?? 'pengguna terhapus' }}
                            @else
                                Belum ditinjau
                            @endif
                        </p>
                    </div>

                </div>

            </div>


            {{-- ISI MASUKAN --}}
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden">

                <div class="border-b border-gray-200 px-6 py-5">
                    <h2 class="text-lg font-semibold text-gray-900">Isi Masukan</h2>
                </div>

                <div class="px-6 py-6">
                    <div class="whitespace-pre-line break-words rounded-lg bg-gray-50 p-4 text-gray-800">{{ $feedback->message }}</div>
                </div>

            </div>


            {{-- IDENTITAS (OPSIONAL) --}}
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden">

                <div class="border-b border-gray-200 px-6 py-5">
                    <h2 class="text-lg font-semibold text-gray-900">Identitas Pengirim (Opsional)</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Hanya untuk tindak lanjut internal. Jangan dibagikan atau dipublikasikan.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 px-6 py-6">

                    <div>
                        <p class="text-sm text-gray-500">Nama</p>
                        <p class="mt-1 font-semibold text-gray-900 break-words">{{ $feedback->name ?: 'Tidak diisi' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="mt-1 font-semibold text-gray-900 break-words">{{ $feedback->email ?: 'Tidak diisi' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Nomor Telepon</p>
                        <p class="mt-1 font-semibold text-gray-900 break-words">{{ $feedback->phone ?: 'Tidak diisi' }}</p>
                    </div>

                </div>

            </div>


            {{-- FORM TINDAK LANJUT --}}
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden">

                <div class="border-b border-gray-200 px-6 py-5">
                    <h2 class="text-lg font-semibold text-gray-900">Tindak Lanjut</h2>
                    <p class="text-sm text-gray-500 mt-1">Perbarui status dan catatan tindak lanjut masukan.</p>
                </div>

                @cannot('update-feedback')
                    <div class="px-6 py-6 space-y-4">
                        <p class="rounded-lg bg-yellow-50 border border-yellow-200 px-4 py-3 text-sm text-yellow-800">
                            Akun Anda hanya memiliki akses baca untuk Kritik &amp; Saran.
                        </p>
                        <div>
                            <p class="text-sm text-gray-500">Catatan Admin</p>
                            <div class="mt-1 whitespace-pre-line break-words text-gray-900">{{ $feedback->admin_note ?: 'Belum ada catatan' }}</div>
                        </div>
                    </div>
                @else
                <form method="POST" action="{{ route('feedback.update', $feedback) }}" class="px-6 py-6 space-y-6">

                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Status</label>

                        <select id="status" name="status" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            @foreach ($statusLabels as $value => [$label])
                                <option value="{{ $value }}" @selected(old('status', $feedback->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="admin_note" class="block text-sm font-semibold text-gray-700 mb-2">Catatan Admin</label>

                        <textarea id="admin_note" name="admin_note" rows="6" maxlength="5000"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Catatan tindak lanjut internal...">{{ old('admin_note', $feedback->admin_note) }}</textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between gap-3">

                        <a href="{{ route('feedback.index') }}"
                            class="inline-flex justify-center items-center rounded-lg bg-gray-600 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-700">
                            Kembali ke Daftar
                        </a>

                        <button type="submit"
                            class="inline-flex justify-center items-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                            Simpan Perubahan
                        </button>

                    </div>

                </form>
                @endcannot

            </div>

        </div>

    </div>

</x-app-layout>
